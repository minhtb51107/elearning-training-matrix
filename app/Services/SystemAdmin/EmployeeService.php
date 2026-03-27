<?php

namespace App\Services\SystemAdmin;

use App\Models\User;
use App\Models\ClassEnrollment;
use App\Enums\RoleEnum;
use App\Enums\EnrollmentStatusEnum;

class EmployeeService
{
    public function getFilteredEmployees(array $filters)
    {
        // 👉 Đã đổi 'role', '!=', 1 thành RoleEnum::SYSTEM_ADMIN->value
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

        $employees = $query->paginate(15)->withQueryString();
        
        $employees->getCollection()->transform(function ($user) {
            $enrollments = ClassEnrollment::with('courseClass.course')
                ->where('user_id', $user->id)
                ->get();

            // 👉 Đã đổi chuỗi cứng thành Enum
            $learning = $enrollments->whereIn('status', [
                EnrollmentStatusEnum::ENROLLED->value, 
                EnrollmentStatusEnum::IN_PROGRESS->value
            ]);
            $completed = $enrollments->where('status', EnrollmentStatusEnum::COMPLETED->value);

            $user->activeClasses = $learning->map(fn($en) => [
                'id' => $en->courseClass->id ?? 0,
                'course_name' => $en->courseClass->course->name ?? '--',
                'class_name' => $en->courseClass->name ?? '--',
                'progress' => $en->progress_percent ?? 0
            ])->values();

            $user->history = $completed->map(fn($en) => [
                'id' => $en->courseClass->id ?? 0,
                'course_name' => $en->courseClass->course->name ?? '--',
                'class_name' => $en->courseClass->name ?? '--',
                'date' => $en->updated_at->format('d/m/Y')
            ])->values();

            $user->overview = [
                'learning' => $learning->count(),
                'completed' => $completed->count(),
            ];

            return $user;
        });

        return $employees;
    }
}