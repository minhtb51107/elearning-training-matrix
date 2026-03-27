<?php

namespace App\Services\SystemAdmin;

use App\Models\TrainingRequest;
use App\Models\Course;
use App\Models\CourseClass;
use App\Enums\RequestStatusEnum;
use Carbon\Carbon;

class DashboardService
{
    public function getDashboardData(array $filters): array
    {
        $keyword = $filters['keyword'] ?? null;
        $fromDate = $filters['from_date'] ?? null;
        $toDate = $filters['to_date'] ?? null;

        $filterRequests = function($q) use ($keyword, $fromDate, $toDate) {
            if ($keyword) $q->where('course_name', 'like', '%' . $keyword . '%');
            if ($fromDate) $q->whereDate('created_at', '>=', $fromDate);
            if ($toDate) $q->whereDate('created_at', '<=', $toDate);
        };

        $filterCourses = function($q) use ($keyword, $fromDate, $toDate) {
            if ($keyword) $q->where('name', 'like', '%' . $keyword . '%');
            if ($fromDate) $q->whereDate('created_at', '>=', $fromDate);
            if ($toDate) $q->whereDate('created_at', '<=', $toDate);
        };

        $filterClasses = function($q) use ($keyword, $fromDate, $toDate) {
            if ($keyword) {
                $q->where(function($subQ) use ($keyword) {
                    $subQ->where('name', 'like', '%' . $keyword . '%')
                         ->orWhere('code', 'like', '%' . $keyword . '%');
                });
            }
            if ($fromDate) $q->whereDate('start_date', '>=', $fromDate);
            if ($toDate) $q->whereDate('start_date', '<=', $toDate);
        };

        return [
            'sysStats' => [
                'total_requests' => TrainingRequest::where($filterRequests)->count(),
                'pending_requests' => TrainingRequest::where($filterRequests)->where('status', RequestStatusEnum::PENDING->value)->count(),
                'created_courses' => Course::where($filterCourses)->count(),
                'opened_classes' => CourseClass::where($filterClasses)->where('status', '!=', 'Nháp')->count(),
                'participated_employees' => CourseClass::has('enrollments')->withCount('enrollments')->get()->sum('enrollments_count')
            ],
            'sysPendingRequests' => TrainingRequest::with('department')
                ->where($filterRequests)
                ->where('status', RequestStatusEnum::PENDING->value)
                ->latest()
                ->take(5)
                ->get()
                ->map(fn ($req) => [
                    'department' => $req->department ? $req->department->name : '--',
                    'name' => $req->course_name,
                    'date' => $req->created_at->format('d/m/Y'),
                    'status' => 'Chờ duyệt',
                ]),
            'sysUpcomingClasses' => CourseClass::with(['course', 'department'])
                ->withCount('enrollments')
                ->where($filterClasses)
                ->whereIn('status', ['Mở đăng ký', 'Đang học']) 
                ->orderBy('start_date', 'asc')
                ->take(5)
                ->get()
                ->map(fn ($cls) => [
                    'code' => $cls->code,
                    'name' => $cls->name,
                    'course' => $cls->course ? $cls->course->name : '--',
                    'department' => $cls->department ? $cls->department->name : 'Toàn công ty',
                    'date' => $cls->start_date ? Carbon::parse($cls->start_date)->format('d/m/Y') : '--',
                    'students' => $cls->enrollments_count ?? 0,
                ])
        ];
    }
}