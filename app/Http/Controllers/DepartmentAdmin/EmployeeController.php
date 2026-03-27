<?php

namespace App\Http\Controllers\DepartmentAdmin;

use App\Http\Controllers\Controller;
use App\Services\DepartmentAdmin\EmployeeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $filters = $request->only(['keyword', 'status']);
        $employees = $this->employeeService->getFilteredEmployees(Auth::user()->department_id, $filters);

        return Inertia::render('DepartmentAdmin/Employees/Index', [
            'employees' => $employees,
            'filters' => $filters
        ]);
    }
}