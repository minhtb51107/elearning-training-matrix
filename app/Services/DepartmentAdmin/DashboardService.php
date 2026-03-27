<?php

namespace App\Services\DepartmentAdmin;

use App\Models\TrainingRequest;
use App\Models\Course;
use App\Enums\RequestStatusEnum;

class DashboardService
{
    public function getDashboardData($departmentId, array $filters): array
    {
        $keyword = $filters['keyword'] ?? null;
        $fromDate = $filters['from_date'] ?? null;
        $toDate = $filters['to_date'] ?? null;

        $filterRequests = function($q) use ($departmentId, $keyword, $fromDate, $toDate) {
            $q->where('department_id', $departmentId);
            if ($keyword) $q->where('course_name', 'like', '%' . $keyword . '%');
            if ($fromDate) $q->whereDate('created_at', '>=', $fromDate);
            if ($toDate) $q->whereDate('created_at', '<=', $toDate);
        };

        $filterCourses = function($q) use ($departmentId, $keyword, $fromDate, $toDate) {
            $q->whereHas('departments', function($q2) use ($departmentId) {
                $q2->where('departments.id', $departmentId);
            });
            if ($keyword) $q->where('name', 'like', '%' . $keyword . '%');
            if ($fromDate) $q->whereDate('created_at', '>=', $fromDate);
            if ($toDate) $q->whereDate('created_at', '<=', $toDate);
        };

        return [
            'deptStats' => [
                'total_requests' => TrainingRequest::where($filterRequests)->count(),
                'pending_requests' => TrainingRequest::where($filterRequests)->where('status', RequestStatusEnum::PENDING->value)->count(),
                'opened_courses' => Course::where($filterCourses)->count(),
                'participated_employees' => 0 
            ],
            'deptRecentRequests' => TrainingRequest::where($filterRequests)
                ->latest()->take(3)->get()->map(fn ($req) => [
                    'name' => $req->course_name,
                    'date' => $req->created_at->format('d/m/Y'),
                    'status' => $req->status,
                ]),
            'deptActiveCourses' => Course::where($filterCourses)
                ->withCount('courseClasses')
                ->latest()->take(2)->get()->map(fn ($course) => [
                    'name' => $course->name,
                    'time' => $course->created_at->format('m/Y'),
                    'classes' => $course->course_classes_count,
                    'employees' => 0, 
                    'status' => ($course->status === 'Chưa có lớp') ? 'Chưa có lớp' : 'Đang mở',
                ])
        ];
    }
}