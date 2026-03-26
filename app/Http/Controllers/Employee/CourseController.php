<?php
namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseClass;
use App\Models\ClassEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use App\Notifications\SystemNotification; // Bổ sung thư viện thông báo

class CourseController extends Controller
{
    public function show($id)
    {
        $course = Course::with([
            'courseClasses' => function($query) {
                $query->where('status', '!=', 'completed')->with('instructor');
            },
            'lessons',
            'documents'
        ])->findOrFail($id);

        $classes = $course->courseClasses->map(function ($cls) {
            $enrolledCount = ClassEnrollment::where('course_class_id', $cls->id)->count();
            $maxStudents = $cls->max_students ?? 30; 
            $isFull = $enrolledCount >= $maxStudents;

            $isEnrolled = ClassEnrollment::where('course_class_id', $cls->id)
                            ->where('user_id', Auth::id())
                            ->exists();

            return [
                'id' => $cls->id,
                'name' => $cls->code,
                'time' => ($cls->start_date ? date('d/m/Y', strtotime($cls->start_date)) : 'Đang cập nhật'),
                'instructor' => $cls->instructor ? $cls->instructor->name : 'Chưa phân công',
                'slot' => $isFull ? 'ĐÃ ĐỦ SỐ LƯỢNG' : ($enrolledCount . '/' . $maxStudents),
                'isFull' => $isFull,
                'isEnrolled' => $isEnrolled
            ];
        });

        return Inertia::render('Employee/Courses/Show', [
            'course' => [
                'id' => $course->id,
                'name' => $course->name,
                'overview' => array_filter([
                    '- Mục tiêu: ' . ($course->description ?: 'Đang cập nhật'),
                    '- Đối tượng: ' . $course->target_audience,
                    '- Hình thức: ' . $course->format,
                    '- Thời lượng: ' . $course->duration . 'h'
                ]),
                'content' => $course->lessons->map(function($lesson) {
                    return [
                        'id' => $lesson->id,
                        'title' => $lesson->title,
                        'type' => $lesson->media_type, 
                        'time' => $lesson->duration > 0 ? $lesson->duration . ' phút' : '--',
                        'url' => $lesson->media_type === 'youtube' 
                            ? $lesson->media_url 
                            : ($lesson->media_url ? Storage::disk('s3')->url($lesson->media_url) : '#')
                    ];
                }),
                'documents' => $course->documents->map(function($doc) {
                    return [
                        'id' => $doc->id,
                        'title' => $doc->title,
                        'url' => $doc->type === 'link' 
                            ? $doc->url 
                            : ($doc->file_path ? Storage::disk('s3')->url($doc->file_path) : '#')
                    ];
                }),
                'classes' => $classes,
            ]
        ]);
    }
    
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = Course::whereHas('departments', function($q) use ($user) {
            $q->where('departments.id', $user->department_id);
        })->latest();

        if ($request->filled('keyword')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->keyword . '%')
                  ->orWhere('code', 'like', '%' . $request->keyword . '%');
            });
        }

        if ($request->filled('type') && $request->type !== 'Tất cả') {
            if ($request->type === 'Bắt buộc') {
                $query->where('target_audience', 'Toàn phòng');
            } else {
                $query->where('target_audience', '!=', 'Toàn phòng');
            }
        }

        $paginator = $query->paginate(6)->withQueryString();

        $paginator->getCollection()->transform(function ($course) {
            return [
                'id' => $course->id,
                'image' => 'https://placehold.co/600x300/0ea5e9/white?text=' . urlencode(mb_strtoupper(mb_substr($course->name, 0, 3, 'UTF-8'))),
                'title' => $course->name,
                'description' => $course->description ?: 'Chưa có mô tả',
                'date' => $course->created_at->format('m/Y'),
                'type' => $course->target_audience === 'Toàn phòng' ? 'Bắt buộc' : 'Tự chọn'
            ];
        });

        return Inertia::render('Employee/Courses/Index', [
            'courses' => $paginator,
            'filters' => $request->only(['keyword', 'type'])
        ]);
    }

    public function enroll(Request $request, $classId)
    {
        $userId = Auth::id();

        $exists = ClassEnrollment::where('course_class_id', $classId)
                    ->where('user_id', $userId)
                    ->exists();

        if (!$exists) {
            ClassEnrollment::create([
                'course_class_id' => $classId,
                'user_id' => $userId,
                'status' => 'enrolled'
            ]);

            // 👉 BẮN THÔNG BÁO XÁC NHẬN GHI DANH
            $user = Auth::user();
            $courseClass = CourseClass::find($classId);
            
            $user->notify(new SystemNotification(
                'Đăng ký thành công',
                'Bạn đã ghi danh thành công vào lớp: <strong>' . ($courseClass->name ?? 'N/A') . '</strong>. Hãy chuẩn bị lịch học nhé!',
                route('employee.my-classes.show', $classId)
            ));
        }

        return back()->with('success', 'Đăng ký lớp học thành công! Vui lòng kiểm tra trong Lớp học của tôi.');
    }
}