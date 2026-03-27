<?php

namespace App\Http\Controllers\SystemAdmin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Department;
use App\Models\TrainingRequest;
use App\Services\SystemAdmin\CourseService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Requests\SystemAdmin\CourseRequest;

class CourseController extends Controller
{
    protected $courseService;

    public function __construct(CourseService $courseService)
    {
        $this->courseService = $courseService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['tab', 'date', 'scope', 'source', 'status', 'keyword']);
        
        return Inertia::render('SystemAdmin/Courses/Index', [
            'courses' => $this->courseService->getFilteredCourses($filters),
            'departments' => Department::select('id', 'name')->orderBy('name')->get(),
            'filters' => $filters
        ]);
    }

    public function create(Request $request)
    {
        return Inertia::render('SystemAdmin/Courses/Create', [
            // 👉 Sửa 'approved' thành RequestStatusEnum
            'approvedRequests' => TrainingRequest::with('department')
                ->where('status', RequestStatusEnum::APPROVED->value)
                ->latest()
                ->get(),
            'preselectedRequestId' => $request->query('request_id') 
        ]);
    }

    public function store(CourseRequest $request)
    {
        $this->courseService->createCourse($request->validated());
        return redirect()->route('system.courses.index')->with('success', 'Đã tạo khóa học thành công!');
    }

    public function update(CourseRequest $request, Course $course)
    {
        $this->courseService->updateCourse($course, $request->validated());
        return redirect()->route('system.courses.index')->with('success', 'Đã cập nhật thông tin khóa học thành công!');
    }
    
    public function show(Course $course)
    {
        return Inertia::render('SystemAdmin/Courses/Show', compact('course'));
    }

    public function edit(Course $course)
    {
        $course->load('documents', 'departments', 'lessons', 'assignments'); 
        return Inertia::render('SystemAdmin/Courses/Edit', [
            'course' => $course,
            'departments' => Department::all() 
        ]);
    }

    public function destroy(Course $course)
    {
        $this->courseService->deleteCourse($course);
        return redirect()->route('system.courses.index')->with('success', 'Đã xóa khóa học an toàn!');
    }
}