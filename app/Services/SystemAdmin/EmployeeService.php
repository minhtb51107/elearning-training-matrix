<?php

namespace App\Services\SystemAdmin;

use App\Models\User;
use App\Enums\RoleEnum;
use App\Enums\EnrollmentStatusEnum;

class EmployeeService
{
    public function getFilteredEmployees(array $filters)
    {
        $query = User::with('department')->where('role', '!=', RoleEnum::SYSTEM_ADMIN->value)->latest();

        if (!empty($filters['department_id']) && $filters['department_id'] !== 'all') {
            $query->where('department_id', $filters['department_id']);
        }
        
        if (!empty($filters['keyword'])) {
            $query->where(function($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['keyword'] . '%')
                  ->orWhere('email', 'like', '%' . $filters['keyword'] . '%');
            });
        }

        // Trả về trực tiếp Pagination, không cần getCollection()->transform() nữa
        return $query->paginate(15)->withQueryString();
    }
}