<?php

namespace App\Services\DepartmentAdmin;

use App\Models\User;
use App\Enums\RoleEnum;
use App\Enums\EnrollmentStatusEnum;

class EmployeeService
{
    public function getFilteredEmployees($departmentId, array $filters)
    {
        $query = User::with('department')
            ->where('department_id', $departmentId)
            ->where('role', RoleEnum::EMPLOYEE->value)
            ->latest();

        if (!empty($filters['keyword'])) {
            $query->where(function($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['keyword'] . '%')
                  ->orWhere('email', 'like', '%' . $filters['keyword'] . '%');
            });
        }

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $status = $filters['status'];
            $query->whereIn('id', function ($subQuery) use ($status) {
                $subQuery->select('user_id')->from('class_enrollments');
                
                if ($status === 'in_progress') {
                    $subQuery->whereIn('status', [EnrollmentStatusEnum::ENROLLED->value, EnrollmentStatusEnum::IN_PROGRESS->value]);
                } elseif (in_array($status, [EnrollmentStatusEnum::COMPLETED->value, EnrollmentStatusEnum::FAILED->value])) {
                    $subQuery->where('status', $status);
                }
            });
        }

        // Trả về trực tiếp Pagination thô
        return $query->paginate(15)->withQueryString();
    }
}