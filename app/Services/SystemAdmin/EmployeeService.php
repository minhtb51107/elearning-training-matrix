<?php

namespace App\Services\SystemAdmin;

use App\Models\User;
use App\Enums\RoleEnum;

class EmployeeService
{
    public function getFilteredEmployees(array $filters)
    {
        $query = User::with('department')->where('role', '!=', RoleEnum::SYSTEM_ADMIN->value)->latest();

        if (!empty($filters['department_id']) && $filters['department_id'] !== 'all') {
            $query->where('department_id', $filters['department_id']);
        }
        
        // Bổ sung logic lọc theo chức vụ (Leader / Staff)
        if (!empty($filters['position']) && $filters['position'] !== 'all') {
            if ($filters['position'] === 'leader') {
                $query->where('is_manager', true);
            } else {
                $query->where('is_manager', false);
            }
        }
        
        if (!empty($filters['keyword'])) {
            $query->where(function($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['keyword'] . '%')
                  ->orWhere('email', 'like', '%' . $filters['keyword'] . '%')
                  ->orWhere('job_title', 'like', '%' . $filters['keyword'] . '%');
            });
        }

        return $query->paginate(15)->withQueryString();
    }

    // Hàm mới: Xử lý lưu thông tin cập nhật HR
    public function updateHrInfo(User $employee, array $data)
    {
        $employee->update([
            'job_title' => $data['job_title'] ?? null,
            'is_manager' => $data['is_manager'] ?? false,
            'join_date' => $data['join_date'] ?? null,
        ]);
        
        return $employee;
    }
}