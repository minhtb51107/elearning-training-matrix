<?php
namespace App\Http\Controllers\DepartmentAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ClassEnrollment; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        // Chỉ lấy nhân sự thuộc phòng ban của Admin đang đăng nhập
        $departmentId = Auth::user()->department_id;
        $query = User::with('department')->where('department_id', $departmentId)->where('role', 3)->latest();

        // 1. Lọc theo từ khóa (Tên hoặc Email)
        if ($request->filled('keyword')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->keyword . '%')
                  ->orWhere('email', 'like', '%' . $request->keyword . '%');
            });
        }

        // =========================================================
        // 2. NỐI DÂY ĐIỆN: BỘ LỌC TRẠNG THÁI HỌC TẬP
        // =========================================================
        if ($request->filled('status') && $request->status !== 'all') {
            $status = $request->status;
            
            // Tìm những User có ID nằm trong danh sách học viên thỏa mãn điều kiện trạng thái
            $query->whereIn('id', function ($subQuery) use ($status) {
                $subQuery->select('user_id')->from('class_enrollments');
                
                if ($status === 'in_progress') {
                    $subQuery->whereIn('status', ['enrolled', 'in_progress']);
                } elseif ($status === 'completed') {
                    $subQuery->where('status', 'completed');
                } elseif ($status === 'failed') {
                    $subQuery->where('status', 'failed');
                }
            });
        }

        $employees = $query->paginate(15)->withQueryString();

        // 3. Rút dữ liệu học tập thật cho từng nhân viên để đổ ra Modal
        $employees->getCollection()->transform(function ($user) {
            $enrollments = ClassEnrollment::with('courseClass.course')
                ->where('user_id', $user->id)
                ->get();

            $learning = $enrollments->whereIn('status', ['enrolled', 'in_progress']);
            $completed = $enrollments->where('status', 'completed');

            $user->activeClasses = $learning->map(function($en) {
                return [
                    'id' => $en->courseClass->id ?? 0,
                    'course_name' => $en->courseClass->course->name ?? '--',
                    'class_name' => $en->courseClass->name ?? '--',
                    'progress' => $en->progress_percent ?? 0
                ];
            })->values();

            $user->history = $completed->map(function($en) {
                return [
                    'id' => $en->courseClass->id ?? 0,
                    'course_name' => $en->courseClass->course->name ?? '--',
                    'class_name' => $en->courseClass->name ?? '--',
                    'date' => $en->updated_at->format('d/m/Y')
                ];
            })->values();

            $user->overview = [
                'learning' => $learning->count(),
                'completed' => $completed->count(),
            ];

            return $user;
        });

        return Inertia::render('DepartmentAdmin/Employees/Index', [
            'employees' => $employees,
            'filters' => $request->only(['keyword', 'status'])
        ]);
    }
}