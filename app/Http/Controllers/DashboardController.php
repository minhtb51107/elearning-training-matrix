<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\TrainingRequest;
use App\Models\Course;
use App\Models\CourseClass;
use App\Models\ClassEnrollment;
use App\Models\ClassSession;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $keyword = $request->input('keyword');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        $dashboardData = [];

        // ==========================================
        // 1. DÀNH CHO ADMIN HỆ THỐNG (ROLE 1)
        // ==========================================
        if ($user->role === 1) {
            // ... (Giữ nguyên logic của role 1)
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

            $dashboardData['sysStats'] = [
                'total_requests' => TrainingRequest::where($filterRequests)->count(),
                'pending_requests' => TrainingRequest::where($filterRequests)->where('status', 'pending')->count(),
                'created_courses' => Course::where($filterCourses)->count(),
                'opened_classes' => CourseClass::where($filterClasses)->where('status', '!=', 'NHÁP')->count(),
                'participated_employees' => CourseClass::has('enrollments')->withCount('enrollments')->get()->sum('enrollments_count')
            ];

            $dashboardData['sysPendingRequests'] = TrainingRequest::with('department')
                ->where($filterRequests)
                ->where('status', 'pending')
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($req) {
                    return [
                        'department' => $req->department ? $req->department->name : '--',
                        'name' => $req->course_name,
                        'date' => $req->created_at->format('d/m/Y'),
                        'status' => 'Chờ duyệt',
                    ];
                });

            $dashboardData['sysUpcomingClasses'] = CourseClass::with(['course', 'department'])
                ->withCount('enrollments')
                ->where($filterClasses)
                ->whereIn('status', ['MỞ ĐĂNG KÝ', 'ĐANG HỌC']) 
                ->orderBy('start_date', 'asc')
                ->take(5)
                ->get()
                ->map(function ($cls) {
                    return [
                        'code' => $cls->code,
                        'name' => $cls->name,
                        'course' => $cls->course ? $cls->course->name : '--',
                        'department' => $cls->department ? $cls->department->name : 'Toàn công ty',
                        'date' => $cls->start_date ? \Carbon\Carbon::parse($cls->start_date)->format('d/m/Y') : '--',
                        'students' => $cls->enrollments_count ?? 0,
                    ];
                });
        } 
        // ==========================================
        // 2. DÀNH CHO ADMIN PHÒNG BAN (ROLE 2)
        // ==========================================
        elseif ($user->role === 2) {
            // ... (Giữ nguyên logic role 2)
            $departmentId = $user->department_id;
            
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

            $dashboardData['deptStats'] = [
                'total_requests' => TrainingRequest::where($filterRequests)->count(),
                'pending_requests' => TrainingRequest::where($filterRequests)->where('status', 'pending')->count(),
                'opened_courses' => Course::where($filterCourses)->count(),
                'participated_employees' => 0 
            ];

            $dashboardData['deptRecentRequests'] = TrainingRequest::where($filterRequests)
                ->latest()->take(3)->get()->map(function ($req) {
                    return [
                        'name' => $req->course_name,
                        'date' => $req->created_at->format('d/m/Y'),
                        'status' => $req->status,
                    ];
                });

            $dashboardData['deptActiveCourses'] = Course::where($filterCourses)
                ->withCount('courseClasses')
                ->latest()->take(2)->get()->map(function ($course) {
                    return [
                        'name' => $course->name,
                        'time' => $course->created_at->format('m/Y'),
                        'classes' => $course->course_classes_count,
                        'employees' => 0, 
                        'status' => ($course->status === 'draft' || $course->status === 'pending') ? 'Chưa có lớp' : 'Đang mở',
                    ];
                });
        }
        // ==========================================
        // 3. DÀNH CHO NHÂN VIÊN (ROLE 3) - THÊM MỚI
        // ==========================================
        elseif ($user->role === 3) {
            $user->load('department');
            $today = Carbon::today();

            // Lấy tổng số lớp Đang học
            $learningCount = ClassEnrollment::where('user_id', $user->id)
                ->whereIn('status', ['enrolled', 'in_progress'])
                ->count();

            // Lấy tổng số lớp Sắp học (Chưa bắt đầu)
            $upcomingCount = ClassEnrollment::where('user_id', $user->id)
                ->whereIn('status', ['enrolled', 'in_progress'])
                ->whereHas('courseClass', function($q) use ($today) {
                    $q->whereDate('start_date', '>=', $today);
                })->count();

            $dashboardData['employeeStats'] = [
                'learning' => $learningCount,
                'upcoming' => $upcomingCount,
                'department' => $user->department ? $user->department->name : '--',
            ];

            // 3 Lớp sắp diễn ra
            $dashboardData['upcomingClasses'] = ClassEnrollment::with(['courseClass.instructor', 'courseClass.sessions'])
                ->where('user_id', $user->id)
                ->whereIn('status', ['enrolled', 'in_progress'])
                ->whereHas('courseClass', function($q) use ($today) {
                    $q->whereDate('start_date', '>=', $today);
                })
                ->get()
                ->sortBy(function($enrollment) {
                    return $enrollment->courseClass->start_date;
                })
                ->take(3)
                ->values()
                ->map(function ($enrollment) use ($today) {
                    $cls = $enrollment->courseClass;
                    $startDate = Carbon::parse($cls->start_date);
                    
                    if ($startDate->isToday()) {
                        $dateStr = 'Hôm nay - ' . $startDate->format('d/m/Y');
                        $canJoin = true;
                    } elseif ($startDate->isTomorrow()) {
                        $dateStr = 'Ngày mai - ' . $startDate->format('d/m/Y');
                        $canJoin = true; // Cho phép xem bài trước 1 ngày
                    } else {
                        $dateStr = $startDate->format('d/m/Y');
                        $canJoin = false;
                    }

                    // Ưu tiên lấy giờ của buổi học đầu tiên
                    $firstSession = $cls->sessions->sortBy('date')->first();
                    $timeStr = $firstSession ? Carbon::parse($firstSession->start_time)->format('H:i') . ' - ' . Carbon::parse($firstSession->end_time)->format('H:i') : 'Theo lịch phân công';
                    $formatStr = $firstSession ? $firstSession->format . ($firstSession->room ? ' - Phòng ' . $firstSession->room : '') : $cls->format;

                    return [
                        'id' => $cls->id,
                        'date' => $dateStr,
                        'title' => $cls->name,
                        'time' => $timeStr,
                        'format' => $formatStr,
                        'instructor' => $cls->instructor ? $cls->instructor->name : '--',
                        'action' => $canJoin ? 'Vào lớp học' : 'Sắp diễn ra',
                        'canJoin' => $canJoin,
                        'url' => route('employee.my-classes.show', $cls->id)
                    ];
                });

            // 3 Lớp đang học dở dang
            $dashboardData['inProgressClasses'] = ClassEnrollment::with(['courseClass.course'])
                ->where('user_id', $user->id)
                ->whereIn('status', ['enrolled', 'in_progress'])
                ->latest('updated_at')
                ->take(3)
                ->get()
                ->map(function ($enrollment) {
                    $cls = $enrollment->courseClass;
                    return [
                        'id' => $cls->id,
                        'course' => $cls->course ? $cls->course->name : '--',
                        'class' => $cls->name,
                        'progress' => $enrollment->progress_percent ?? 0,
                        'url' => route('employee.my-classes.show', $cls->id)
                    ];
                });
        }

        return Inertia::render('Dashboard', [
            'dashboardData' => $dashboardData,
            'filters' => $request->only(['keyword', 'from_date', 'to_date']) 
        ]);
    }
}