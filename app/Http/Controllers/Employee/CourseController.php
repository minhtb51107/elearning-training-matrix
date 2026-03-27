<?php
namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Services\Employee\CourseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CourseController extends Controller
{
    protected $courseService;

    public function __construct(CourseService $courseService)
    {
        $this->courseService = $courseService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['keyword', 'type']);
        $courses = $this->courseService->getAvailableCourses(Auth::user(), $filters);

        return Inertia::render('Employee/Courses/Index', [
            'courses' => $courses,
            'filters' => $filters
        ]);
    }

    public function show($id)
    {
        $courseData = $this->courseService->getCourseDetails($id, Auth::id());

        return Inertia::render('Employee/Courses/Show', [
            'course' => $courseData
        ]);
    }

    public function enroll(Request $request, $classId)
    {
        $enrolled = $this->courseService->enrollUser($classId, Auth::user());

        if ($enrolled) {
            return back()->with('success', 'Đăng ký lớp học thành công! Vui lòng kiểm tra trong Lớp học của tôi.');
        }

        return back()->with('error', 'Bạn đã đăng ký lớp học này từ trước.');
    }
}