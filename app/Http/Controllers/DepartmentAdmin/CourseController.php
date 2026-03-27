<?php

namespace App\Http\Controllers\DepartmentAdmin;

use App\Http\Controllers\Controller;
use App\Services\DepartmentAdmin\CourseService;
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
        $filters = $request->only(['keyword', 'from_date', 'to_date', 'format', 'status']);
        $courses = $this->courseService->getFilteredCourses(Auth::user()->department_id, $filters);

        return Inertia::render('DepartmentAdmin/Courses/Index', [
            'courses' => $courses,
            'filters' => $filters
        ]);
    }

    public function show($id)
    {
        $data = $this->courseService->getCourseDetails($id);

        return Inertia::render('DepartmentAdmin/Courses/Show', [
            'courseInfo' => $data['courseInfo'],
            'classes' => $data['classes'],
            'materials' => $data['materials']
        ]);
    }
}