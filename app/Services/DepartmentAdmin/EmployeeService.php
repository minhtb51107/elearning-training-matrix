<?php

namespace App\Services\DepartmentAdmin;

use App\Models\User;
use App\Models\ClassEnrollment;
use App\Enums\RoleEnum;
use App\Enums\EnrollmentStatusEnum;

class EmployeeService
{
    public function getFilteredEmployees($departmentId, array $filters)
    {
        // 👉 Đã đổi số 3 thành RoleEnum::EMPLOYEE->value
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
                    // 👉 Dùng Enum
                    $subQuery->whereIn('status', [EnrollmentStatusEnum::ENROLLED->value, EnrollmentStatusEnum::IN_PROGRESS->value]);
                } elseif (in_array($status, [EnrollmentStatusEnum::COMPLETED->value, EnrollmentStatusEnum::FAILED->value])) {
                    $subQuery->where('status', $status);
                }
            });
        }

        $employees = $query->paginate(15)->withQueryString();

        // Rút dữ liệu học tập
        $employees->getCollection()->transform(function ($user) {
            $enrollments = ClassEnrollment::with('courseClass.course')
                ->where('user_id', $user->id)
                ->get();

            // 👉 Dùng Enum
            $learning = $enrollments->whereIn('status', [EnrollmentStatusEnum::ENROLLED->value, EnrollmentStatusEnum::IN_PROGRESS->value]);
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