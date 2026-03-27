<?php

namespace App\Services\Employee;

use App\Models\CourseClass;
use App\Models\ClassEnrollment;
use App\Models\LessonCompletion;
use App\Models\CourseLesson;
use App\Models\Submission;
use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class MyClassService
{
public function getMyClassesList($userId, array $filters)
    {
        $query = ClassEnrollment::with(['courseClass.course'])->where('user_id', $userId)->latest();

        if (!empty($filters['keyword'])) {
            $keyword = $filters['keyword'];
            $query->whereHas('courseClass', function($q) use ($keyword) {
                $q->where('code', 'like', "%$keyword%")
                  ->orWhereHas('course', function($q2) use ($keyword) {
                      $q2->where('name', 'like', "%$keyword%");
                  });
            });
        }

        if (!empty($filters['status']) && $filters['status'] !== 'Tất cả') {
            if ($filters['status'] === 'Đang học') {
                // 👉 Enum
                $query->whereIn('status', [EnrollmentStatusEnum::ENROLLED->value, EnrollmentStatusEnum::IN_PROGRESS->value]);
            } elseif ($filters['status'] === 'Hoàn thành') {
                // 👉 Enum
                $query->whereIn('status', [EnrollmentStatusEnum::COMPLETED->value, EnrollmentStatusEnum::FAILED->value]);
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

            // 👉 Enum thay thế mảng Map cứng
            $statusMap = [
                EnrollmentStatusEnum::ENROLLED->value => 'Đã đăng ký - Chưa bắt đầu',
                EnrollmentStatusEnum::IN_PROGRESS->value => 'Đang học',
                EnrollmentStatusEnum::COMPLETED->value => 'Đã hoàn thành',
                EnrollmentStatusEnum::FAILED->value => 'Chưa đạt'
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
                // 👉 Enum
                'isFailed' => $enrollment->status === EnrollmentStatusEnum::FAILED->value,
                'btn' => in_array($enrollment->status, [EnrollmentStatusEnum::COMPLETED->value, EnrollmentStatusEnum::FAILED->value]) ? 'Xem kết quả' : 'Bắt đầu học'
            ];
        });

        return $paginator;
    }

    public function getClassDetail($classId, $userId)
    {
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
            return [
                'id' => $doc->id,
                'title' => $doc->title,
                'type' => $doc->type,
                'url' => $doc->type === 'link' ? $doc->url : ($doc->file_path ? Storage::disk('s3')->url($doc->file_path) : '#'),
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

        return [
            'classInfo' => [
                'id' => $courseClass->id,
                'code' => $courseClass->code,
                'name' => $courseClass->name,
                'status' => $enrollment->status,
                'progress' => $enrollment->progress_percent ?? 0
            ],
            'course' => [
                'name' => $course->name,
                'description' => $course->description,
            ],
            'lessons' => $lessons,
            'documents' => $documents,
            'assignments' => $assignments
        ];
    }

    public function processLessonCompletion($courseClassId, $lessonId, $userId)
    {
        LessonCompletion::firstOrCreate([
            'user_id' => $userId,
            'course_lesson_id' => $lessonId,
            'course_class_id' => $courseClassId
        ]);

        $classEnrollment = ClassEnrollment::where('course_class_id', $courseClassId)
            ->where('user_id', $userId)
            ->firstOrFail();

        // 👉 Enum
        if ($classEnrollment->status === EnrollmentStatusEnum::ENROLLED->value) {
            $classEnrollment->status = EnrollmentStatusEnum::IN_PROGRESS->value;
        }

        $courseClass = CourseClass::findOrFail($courseClassId);
        $totalLessons = CourseLesson::where('course_id', $courseClass->course_id)->count();
        $completedLessons = LessonCompletion::where('course_class_id', $courseClassId)
            ->where('user_id', $userId)
            ->count();

        $progress = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0;
        $classEnrollment->progress_percent = $progress;
        $classEnrollment->save();

        return $progress;
    }

    public function processAssignmentSubmission($courseClassId, array $data, $user, $file = null)
    {
        $fileUrl = null;
        if ($file) {
            $path = $file->store('submissions', 's3');
            $fileUrl = Storage::disk('s3')->url($path);
        }

        Submission::updateOrCreate(
            [
                'assignment_id' => $data['assignment_id'],
                'user_id' => $user->id,
                'course_class_id' => $courseClassId,
            ],
            [
                'content' => isset($data['answers']) && is_array($data['answers']) ? json_encode($data['answers'], JSON_UNESCAPED_UNICODE) : null,
                'file_url' => $fileUrl,
                'status' => 'pending', 
                'score' => null,      
                'feedback' => null,    
                'graded_by' => null,
            ]
        );

        $systemAdmins = User::where('role', 1)->get();
        $courseClass = CourseClass::find($courseClassId);
        foreach ($systemAdmins as $admin) {
            $admin->notify(new SystemNotification(
                'Học viên nộp bài tập',
                'Học viên <strong>' . $user->name . '</strong> vừa nộp bài cho lớp <strong>' . ($courseClass->name ?? '') . '</strong>.',
                route('system.grades.index')
            ));
        }
    }
}