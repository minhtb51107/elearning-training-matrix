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

    // Lấy danh sách nhân viên đủ điều kiện để gán vào lớp
    public function getEligibleEmployees($courseClassId)
    {
        $employees = $this->courseService->getEligibleEmployees($courseClassId, Auth::user()->department_id);
        return response()->json($employees);
    }

    // Xử lý lưu thông tin chỉ định nhân viên
    public function assignEmployees(Request $request, $courseClassId)
    {
        $validated = $request->validate([
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:users,id',
            'deadline' => 'nullable|date|after_or_equal:today',
        ]);

        $this->courseService->assignEmployeesToClass(
            $courseClassId, 
            $validated['employee_ids'], 
            $validated['deadline'] ?? null, 
            Auth::id()
        );

        return redirect()->back()->with('success', 'Đã chỉ định nhân viên tham gia lớp học thành công!');
    }
}