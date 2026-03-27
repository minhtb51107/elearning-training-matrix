<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Enums\RoleEnum;
use App\Services\SystemAdmin\DashboardService as SysDashboardService;
use App\Services\DepartmentAdmin\DashboardService as DeptDashboardService;
use App\Services\Employee\DashboardService as EmpDashboardService;

class DashboardController extends Controller
{
    public function __construct(
        protected SysDashboardService $sysDashboard,
        protected DeptDashboardService $deptDashboard,
        protected EmpDashboardService $empDashboard
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $filters = $request->only(['keyword', 'from_date', 'to_date']);
        
        $dashboardData = [];

        // Điều phối lấy dữ liệu theo đúng Role (Đã sử dụng Enum)
        if ($user->role === RoleEnum::SYSTEM_ADMIN) {
            $dashboardData = $this->sysDashboard->getDashboardData($filters);
            
        } elseif ($user->role === RoleEnum::DEPARTMENT_ADMIN) {
            $dashboardData = $this->deptDashboard->getDashboardData($user->department_id, $filters);
            
        } elseif ($user->role === RoleEnum::EMPLOYEE) {
            $dashboardData = $this->empDashboard->getDashboardData($user);
        }

        return Inertia::render('Dashboard', [
            'dashboardData' => $dashboardData,
            'filters' => $filters
        ]);
    }
}