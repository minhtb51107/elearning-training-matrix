<?php
namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\CourseClass;
use App\Models\ClassEnrollment;
use App\Models\LessonCompletion;
use App\Models\CourseLesson;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Carbon\Carbon;
use App\Notifications\SystemNotification; // Bổ sung thư viện thông báo

class MyClassController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();

        $query = ClassEnrollment::with(['courseClass.course'])
            ->where('user_id', $userId)
            ->latest();

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->whereHas('courseClass', function($q) use ($keyword) {
                $q->where('code', 'like', "%$keyword%")
                  ->orWhereHas('course', function($q2) use ($keyword) {
                      $q2->where('name', 'like', "%$keyword%");
                  });
            });
        }

        if ($request->filled('status') && $request->status !== 'Tất cả') {
            if ($request->status === 'Đang học') {
                $query->whereIn('status', ['enrolled', 'in_progress']);
            } elseif ($request->status === 'Hoàn thành') {
                $query->whereIn('status', ['completed', 'failed']);
            }
        }

        $paginator = $query->paginate(6)->withQueryString();

        $paginator->getCollection()->transform(function ($enrollment) {
            $cls = $enrollment->courseClass;
            $course = $cls->course;
            
            $dateStr = 'Chưa xác định';
            if ($cls->start_date && $cls->end_date) {
                $dateStr = Carbon::parse($cls->start_date)->format('m/Y') . ' - ' . Carbon::parse($cls->end_date)->format('m/Y');
            }

            $statusMap = [
                'enrolled' => 'Đã đăng ký - Chưa bắt đầu',
                'in_progress' => 'Đang học',
                'completed' => 'Đã hoàn thành',
                'failed' => 'Chưa đạt'
            ];
            
            $progress = $enrollment->progress_percent ?? 0;

            return [
                'id' => $cls->id,
                'title' => $cls->code,
                'course_name' => $course ? $course->name : '--',
                'description' => $cls->name,
                'date' => $dateStr,
                'statusText' => $statusMap[$enrollment->status] ?? 'Chưa xác định',
                'progress' => $progress,
                'progressText' => $progress . '%',
                'isFailed' => $enrollment->status === 'failed',
                'btn' => ($enrollment->status === 'completed' || $enrollment->status === 'failed') ? 'Xem kết quả' : 'Bắt đầu học'
            ];
        });

        return Inertia::render('Employee/MyClasses/Index', [
            'classes' => $paginator,
            'filters' => $request->only(['keyword', 'status'])
        ]);
    }

    public function show($classId)
    {
        $userId = Auth::id();

        $enrollment = ClassEnrollment::where('course_class_id', $classId)
            ->where('user_id', $userId)
            ->firstOrFail();

        $courseClass = CourseClass::with([
            'course.lessons',
            'course.documents',
            'course.assignments.submissions' => function($query) use ($userId, $classId) {
                $query->where('user_id', $userId)->where('course_class_id', $classId);
            }
        ])->findOrFail($classId);

        $course = $courseClass->course;

        $completedLessonIds = LessonCompletion::where('user_id', $userId)
            ->where('course_class_id', $classId)
            ->pluck('course_lesson_id')
            ->toArray();

        $lessons = $course->lessons->map(function ($lesson) use ($completedLessonIds) {
            $url = '#';
            if ($lesson->media_type === 'youtube') {
                $url = $lesson->media_url;
            } elseif ($lesson->media_url) {
                $url = Storage::disk('s3')->url($lesson->media_url);
            }

            return [
                'id' => $lesson->id,
                'title' => $lesson->title,
                'type' => $lesson->media_type,
                'duration' => $lesson->duration > 0 ? $lesson->duration : 0,
                'url' => $url,
                'isCompleted' => in_array($lesson->id, $completedLessonIds) 
            ];
        });

        $documents = $course->documents->map(function ($doc) {
            $url = $doc->type === 'link' ? $doc->url : ($doc->file_path ? Storage::disk('s3')->url($doc->file_path) : '#');
            return [
                'id' => $doc->id,
                'title' => $doc->title,
                'type' => $doc->type,
                'url' => $url,
            ];
        });

        $assignments = $course->assignments->map(function ($assignment) {
            $submission = $assignment->submissions->first(); 
            
            $decodedQuestions = json_decode($assignment->content, true);
            $questions = is_array($decodedQuestions) ? $decodedQuestions : [$assignment->content];

            return [
                'id' => $assignment->id,
                'title' => $assignment->title,
                'type' => $assignment->type,
                'questions' => $questions, 
                'pass_score' => $assignment->pass_score,
                'submission' => $submission ? [
                    'status' => $submission->status,
                    'score' => $submission->score,
                    'feedback' => $submission->feedback,
                    'answers' => json_decode($submission->content, true) ?? [$submission->content],
                ] : null
            ];
        });

        $progress = $enrollment->progress_percent ?? 0;

        return Inertia::render('Employee/MyClasses/Show', [
            'classInfo' => [
                'id' => $courseClass->id,
                'code' => $courseClass->code,
                'name' => $courseClass->name,
                'status' => $enrollment->status,
                'progress' => $progress
            ],
            'course' => [
                'name' => $course->name,
                'description' => $course->description,
            ],
            'lessons' => $lessons,
            'documents' => $documents,
            'assignments' => $assignments
        ]);
    }

    public function completeLesson(Request $request, $courseClassId)
    {
        $validated = $request->validate([
            'lesson_id' => 'required|exists:course_lessons,id'
        ]);

        $userId = Auth::id();
        $lessonId = $validated['lesson_id'];

        LessonCompletion::firstOrCreate([
            'user_id' => $userId,
            'course_lesson_id' => $lessonId,
            'course_class_id' => $courseClassId
        ]);

        $classEnrollment = ClassEnrollment::where('course_class_id', $courseClassId)
            ->where('user_id', $userId)
            ->firstOrFail();

        if ($classEnrollment->status === 'enrolled') {
            $classEnrollment->status = 'in_progress';
        }

        $courseClass = CourseClass::findOrFail($courseClassId);
        $totalLessons = CourseLesson::where('course_id', $courseClass->course_id)->count();
        $completedLessons = LessonCompletion::where('course_class_id', $courseClassId)
            ->where('user_id', $userId)
            ->count();

        $progress = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0;
        $classEnrollment->progress_percent = $progress;
        $classEnrollment->save();

        return response()->json([
            'success' => true,
            'progress' => $progress,
            'completed_lesson_id' => $lessonId
        ]);
    }

    public function submitAssignment(Request $request, $courseClassId)
    {
        $request->validate([
            'assignment_id' => 'required|exists:assignments,id',
            'answers' => 'required_without:file|array|nullable', 
            'file' => 'nullable|file|max:20480' 
        ]);

        $userId = Auth::id();
        $fileUrl = null;

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('submissions', 's3');
            $fileUrl = Storage::disk('s3')->url($path);
        }

        Submission::updateOrCreate(
            [
                'assignment_id' => $request->assignment_id,
                'user_id' => $userId,
                'course_class_id' => $courseClassId,
            ],
            [
                'content' => is_array($request->answers) ? json_encode($request->answers, JSON_UNESCAPED_UNICODE) : null,
                'file_url' => $fileUrl,
                'status' => 'pending', 
                'score' => null,       
                'feedback' => null,    
                'graded_by' => null,
            ]
        );

        // 👉 THÔNG BÁO CHO ADMIN HỆ THỐNG VÀO CHẤM BÀI
        $systemAdmins = User::where('role', 1)->get();
        $courseClass = CourseClass::find($courseClassId);
        foreach ($systemAdmins as $admin) {
            $admin->notify(new SystemNotification(
                'Học viên nộp bài tập',
                'Học viên <strong>' . Auth::user()->name . '</strong> vừa nộp bài cho lớp <strong>' . ($courseClass->name ?? '') . '</strong>.',
                route('system.grades.index')
            ));
        }

        return back()->with('success', 'Đã nộp bài đánh giá thành công. Vui lòng chờ Giảng viên chấm điểm!');
    }
}