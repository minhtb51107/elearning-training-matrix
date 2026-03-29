<?php
namespace App\Http\Controllers\SystemAdmin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use App\Services\SystemAdmin\EmployeeService;
use App\Http\Resources\EmployeeResource;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmployeeController extends Controller
{
    protected $employeeService;

    public function __construct(EmployeeService $employeeService)
    {
        $this->employeeService = $employeeService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['keyword', 'department_id', 'position', 'status']);
        $employeesRaw = $this->employeeService->getFilteredEmployees($filters);
        
        return Inertia::render('SystemAdmin/Employees/Index', [
            'employees' => EmployeeResource::collection($employeesRaw),
            'departments' => Department::select('id', 'name')->orderBy('name')->get(),
            'filters' => $filters
        ]);
    }

    // Thêm hàm Cập nhật thông tin nhân sự (HR)
    public function updateHrInfo(Request $request, User $employee)
    {
        $validated = $request->validate([
            'job_title' => 'nullable|string|max:255',
            'is_manager' => 'boolean',
            'join_date' => 'nullable|date',
        ]);

        $this->employeeService->updateHrInfo($employee, $validated);

        return redirect()->back()->with('success', 'Đã cập nhật thông tin chức danh cho nhân viên thành công!');
    }
}