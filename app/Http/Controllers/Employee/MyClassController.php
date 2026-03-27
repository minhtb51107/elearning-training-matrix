<?php
namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Services\Employee\MyClassService;
use App\Http\Requests\Employee\CompleteLessonRequest;
use App\Http\Requests\Employee\SubmitAssignmentRequest;
use App\Http\Resources\MyClassResource; // 👉 Import Resource của bạn
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
            'classes' => MyClassResource::collection($classesRaw), // 👉 Bọc Resource ở đây
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
        $this->myClassService->processAssignmentSubmission(
            $courseClassId, 
            $request->validated(), 
            Auth::user(), 
            $request->file('file')
        );

        return back()->with('success', 'Đã nộp bài đánh giá thành công. Vui lòng chờ Giảng viên chấm điểm!');
    }
}