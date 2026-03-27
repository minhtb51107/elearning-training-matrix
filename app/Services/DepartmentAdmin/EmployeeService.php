<?php

namespace App\Services\DepartmentAdmin;

use App\Models\User;
use App\Models\ClassEnrollment;

class EmployeeService
{
    public function getFilteredEmployees($departmentId, array $filters)
    {
        $query = User::with('department')->where('department_id', $departmentId)->where('role', 3)->latest();

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
                    $subQuery->whereIn('status', ['enrolled', 'in_progress']);
                } elseif (in_array($status, ['completed', 'failed'])) {
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

            $learning = $enrollments->whereIn('status', ['enrolled', 'in_progress']);
            $completed = $enrollments->where('status', 'completed');

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