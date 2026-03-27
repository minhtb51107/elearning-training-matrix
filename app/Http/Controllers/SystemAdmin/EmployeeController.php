<?php
namespace App\Http\Controllers\SystemAdmin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Services\SystemAdmin\EmployeeService;
use App\Http\Resources\EmployeeResource; // 👉 Import Resource
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
            // 👉 Bọc Pagination qua Resource
            'employees' => EmployeeResource::collection($employeesRaw),
            'departments' => Department::select('id', 'name')->orderBy('name')->get(),
            'filters' => $filters
        ]);
    }
}