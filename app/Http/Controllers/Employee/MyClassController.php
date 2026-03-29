<?php
namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Services\Employee\MyClassService;
use App\Http\Requests\Employee\CompleteLessonRequest;
use App\Http\Requests\Employee\SubmitAssignmentRequest;
use App\Http\Resources\MyClassResource; 
use App\Models\CourseClass; // 👉 Thêm Model này để query
use App\Models\ClassEnrollment; // 👉 Thêm Model này để query
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MyClassController extends Controller
{
    protected $myClassService;

    public function __construct(MyClassService $myClassService)
    {
        $this->myClassService = $myClassService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['keyword', 'status']);
        $classesRaw = $this->myClassService->getMyClassesList(Auth::id(), $filters);

        return Inertia::render('Employee/MyClasses/Index', [
            'classes' => MyClassResource::collection($classesRaw), 
            'filters' => $filters
        ]);
    }

    public function show($classId)
    {
        $data = $this->myClassService->getClassDetail($classId, Auth::id());
        return Inertia::render('Employee/MyClasses/Show', $data);
    }

    public function completeLesson(CompleteLessonRequest $request, $courseClassId)
    {
        $validated = $request->validated();
        
        $progress = $this->myClassService->processLessonCompletion($courseClassId, $validated['lesson_id'], Auth::id());

        return response()->json([
            'success' => true,
            'progress' => $progress,
            'completed_lesson_id' => $validated['lesson_id']
        ]);
    }

    public function submitAssignment(SubmitAssignmentRequest $request, $courseClassId)
    {
        // 👉 1. KIỂM TRA ĐIỀU KIỆN TRẮC NGHIỆM (Chặn từ Backend)
        $enrollment = ClassEnrollment::where('course_class_id', $courseClassId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $courseClass = CourseClass::with('course.quizzes')->findOrFail($courseClassId);
        $quizzes = $courseClass->course->quizzes;
        
        // Nếu khóa học có bài Trắc nghiệm, bắt buộc phải duyệt xem đã Passed hết chưa
        if ($quizzes->isNotEmpty()) {
            foreach ($quizzes as $quiz) {
                // Check xem user đã có lượt làm bài (attempt) nào mang status 'passed' chưa
                $hasPassed = $quiz->attempts()
                    ->where('class_enrollment_id', $enrollment->id)
                    ->where('status', 'passed')
                    ->exists();

                if (!$hasPassed) {
                    // Nếu có 1 bài quiz chưa pass -> Văng lỗi ngay lập tức, từ chối nhận bài Tự luận
                    return back()->with('error', 'CẢNH BÁO: Bạn phải thi ĐẠT tất cả bài Trắc nghiệm thì mới được nộp bài Thực hành!');
                }
            }
        }

        // 👉 2. NẾU ĐÃ PASS HẾT (Hoặc khóa học không có trắc nghiệm) -> LƯU BÀI
        $this->myClassService->processAssignmentSubmission(
            $courseClassId, 
            $request->validated(), 
            Auth::user(), 
            $request->file('file')
        );

        return back()->with('success', 'Đã nộp bài đánh giá thành công. Vui lòng chờ Giảng viên chấm điểm!');
    }
}