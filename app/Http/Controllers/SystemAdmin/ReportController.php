<?php

namespace App\Http\Controllers\SystemAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Course;
use App\Models\CourseClass;
use App\Models\ClassEnrollment;
use App\Models\Department;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // 1. TÍNH TOÁN CON SỐ TỔNG QUAN (KPI CHUNG)
        $totalCourses = Course::count();
        $totalClasses = CourseClass::count();
        $totalEnrollments = ClassEnrollment::count(); // Tổng lượt học viên

        $completedCount = ClassEnrollment::where('status', 'completed')->count();
        $failedCount = ClassEnrollment::where('status', 'failed')->count();
        
        $completedPercent = $totalEnrollments > 0 ? round(($completedCount / $totalEnrollments) * 100) : 0;
        $failedPercent = $totalEnrollments > 0 ? round(($failedCount / $totalEnrollments) * 100) : 0;

        $stats = [
            'courses' => $totalCourses,
            'classes' => $totalClasses,
            'students' => number_format($totalEnrollments, 0, ',', '.'),
            'completed_percent' => $completedPercent . '%',
            'failed_percent' => $failedPercent . '%'
        ];

        // 2. TÍNH TOÁN KPI THEO TỪNG PHÒNG BAN (Gom nhóm dữ liệu)
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
                // Giả sử quy định công ty: Tỷ lệ hoàn thành >= 80% là Đạt KPI
                'status' => $percent >= 80 ? 'Đạt KPI' : 'Chưa đạt' 
            ];
        }

        // Sắp xếp phòng ban có nhiều lượt học nhất lên đầu
        usort($deptKPIs, fn($a, $b) => $b['students'] <=> $a['students']);

        // 3. TÍNH TOÁN KPI THEO TỪNG KHÓA HỌC
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

        // Sắp xếp khóa học có nhiều lượt học nhất lên đầu
        usort($courseKPIs, fn($a, $b) => $b['students'] <=> $a['students']);

        // Đẩy dữ liệu ra giao diện (Chỉ lấy top 10 dòng để báo cáo gọn gàng)
        return Inertia::render('SystemAdmin/Reports/Index', [
            'stats' => $stats,
            'deptKPIs' => array_slice($deptKPIs, 0, 10),
            'courseKPIs' => array_slice($courseKPIs, 0, 10)
        ]);
    }
}