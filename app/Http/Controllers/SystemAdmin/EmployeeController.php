<?php
namespace App\Http\Controllers\SystemAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Department;
use App\Models\ClassEnrollment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('department')->where('role', '!=', 1)->latest();

        // Lọc theo Phòng ban
        if ($request->filled('department_id') && $request->department_id !== 'all') {
            $query->where('department_id', $request->department_id);
        }

        // Lọc theo Từ khóa
        if ($request->filled('keyword')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->keyword . '%')
                  ->orWhere('email', 'like', '%' . $request->keyword . '%');
            });
        }

        $employees = $query->paginate(15)->withQueryString();
        
        // NỐI DÂY ĐIỆN: Rút dữ liệu học tập thật cho từng nhân viên
        $employees->getCollection()->transform(function ($user) {
            // Lấy toàn bộ lịch sử đăng ký lớp học của user này
            $enrollments = ClassEnrollment::with('courseClass.course')
                ->where('user_id', $user->id)
                ->get();

            // Phân loại: Đang học
            $learning = $enrollments->whereIn('status', ['enrolled', 'in_progress']);
            
            // Phân loại: Đã hoàn thành
            $completed = $enrollments->where('status', 'completed');

            // Format mảng lớp Đang học
            $user->activeClasses = $learning->map(function($en) {
                return [
                    'id' => $en->courseClass->id ?? 0,
                    'course_name' => $en->courseClass->course->name ?? '--',
                    'class_name' => $en->courseClass->name ?? '--',
                    'progress' => $en->progress_percent ?? 0
                ];
            })->values();

            // Format mảng lớp Đã hoàn thành (Lịch sử)
            $user->history = $completed->map(function($en) {
                return [
                    'id' => $en->courseClass->id ?? 0,
                    'course_name' => $en->courseClass->course->name ?? '--',
                    'class_name' => $en->courseClass->name ?? '--',
                    'date' => $en->updated_at->format('d/m/Y')
                ];
            })->values();

            // Tổng hợp con số thống kê
            $user->overview = [
                'learning' => $learning->count(),
                'completed' => $completed->count(),
            ];

            return $user;
        });

        // Lấy danh sách phòng ban thật
        $departments = Department::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('SystemAdmin/Employees/Index', [
            'employees' => $employees,
            'departments' => $departments,
            'filters' => $request->only(['keyword', 'department_id', 'position', 'status'])
        ]);
    }
}