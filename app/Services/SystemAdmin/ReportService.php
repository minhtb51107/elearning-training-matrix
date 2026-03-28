<?php

namespace App\Services\SystemAdmin;

use App\Models\Course;
use App\Models\CourseClass;
use App\Models\ClassEnrollment;
use App\Models\Department;
use App\Enums\EnrollmentStatusEnum;

class ReportService
{
    public function getDashboardReports()
    {
        $totalCourses = Course::count();
        $totalClasses = CourseClass::count();
        $totalEnrollments = ClassEnrollment::count();

        $completedCount = ClassEnrollment::where('status', EnrollmentStatusEnum::COMPLETED->value)->count();
        $failedCount = ClassEnrollment::where('status', EnrollmentStatusEnum::FAILED->value)->count();
        
        $completedPercent = $totalEnrollments > 0 ? round(($completedCount / $totalEnrollments) * 100) : 0;
        $failedPercent = $totalEnrollments > 0 ? round(($failedCount / $totalEnrollments) * 100) : 0;

        $stats = [
            'courses' => $totalCourses,
            'classes' => $totalClasses,
            'students' => number_format($totalEnrollments, 0, ',', '.'),
            'completed_percent' => $completedPercent . '%',
            'failed_percent' => $failedPercent . '%'
        ];

        // KPI THEO PHÒNG BAN
        $deptKPIs = [];
        $departments = Department::with('users')->get();
        foreach ($departments as $dept) {
            $userIds = $dept->users->pluck('id');
            if ($userIds->isEmpty()) continue;

            $enrollments = ClassEnrollment::whereIn('user_id', $userIds)->get();
            $students = $enrollments->count();
            if ($students == 0) continue;

            $classesCount = $enrollments->pluck('course_class_id')->unique()->count();
            $completed = $enrollments->where('status', 'completed')->count();
            $percent = round(($completed / $students) * 100);

            $deptKPIs[] = [
                'department' => $dept->name,
                'classes' => $classesCount,
                'students' => $students,
                'completed' => $completed,
                'percent' => $percent . '%',
                'status' => $percent >= 80 ? 'Đạt KPI' : 'Chưa đạt' 
            ];
        }
        usort($deptKPIs, fn($a, $b) => $b['students'] <=> $a['students']);

        // KPI THEO KHÓA HỌC
        $courseKPIs = [];
        $courses = Course::with('courseClasses.enrollments')->get();
        foreach ($courses as $course) {
            $classes = $course->courseClasses;
            if ($classes->isEmpty()) continue;

            $classCount = $classes->count();
            $allEnrollments = collect();
            
            foreach ($classes as $cls) {
                $allEnrollments = $allEnrollments->merge($cls->enrollments);
            }

            $students = $allEnrollments->count();
            if ($students == 0) continue;

            $completed = $allEnrollments->where('status', 'completed')->count();
            $percent = round(($completed / $students) * 100);

            $courseKPIs[] = [
                'course' => $course->name,
                'classes' => $classCount,
                'students' => $students,
                'completed' => $completed,
                'percent' => $percent . '%'
            ];
        }
        usort($courseKPIs, fn($a, $b) => $b['students'] <=> $a['students']);

        return [
            'stats' => $stats,
            'deptKPIs' => array_slice($deptKPIs, 0, 10),
            'courseKPIs' => array_slice($courseKPIs, 0, 10)
        ];
    }
}