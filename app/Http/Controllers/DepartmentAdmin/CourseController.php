<?php
namespace App\Http\Controllers\DepartmentAdmin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        // Lấy danh sách khóa học được phân bổ cho Phòng ban của Admin này
        $departmentId = Auth::user()->department_id;
        
        // Load Khóa học và đếm số lượng lớp học (mối quan hệ courseClasses)
        $query = Course::withCount('courseClasses')->whereHas('departments', function ($q) use ($departmentId) {
            $q->where('departments.id', $departmentId);
        })->latest();

        // 1. Lọc theo Từ khóa (Tên hoặc Mã KH)
        if ($request->filled('keyword')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->keyword . '%')
                  ->orWhere('code', 'like', '%' . $request->keyword . '%');
            });
        }

        // 2. Lọc theo Hình thức
        if ($request->filled('format') && $request->format !== 'all') {
            $query->where('format', $request->format);
        }

        // 3. Lọc theo Trạng thái
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // 4. Lọc theo Từ ngày - Đến ngày (Dựa trên ngày tạo)
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $courses = $query->paginate(15)->withQueryString();

        return Inertia::render('DepartmentAdmin/Courses/Index', [
            'courses' => $courses,
            'filters' => $request->only(['keyword', 'from_date', 'to_date', 'format', 'status'])
        ]);
    }
}