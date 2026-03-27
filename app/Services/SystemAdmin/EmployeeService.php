<?php

namespace App\Services\SystemAdmin;

use App\Models\User;
use App\Models\ClassEnrollment;

class EmployeeService
{
    public function getFilteredEmployees(array $filters)
    {
        $query = User::with('department')->where('role', '!=', 1)->latest();

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