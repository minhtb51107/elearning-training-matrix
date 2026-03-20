<?php
namespace App\Http\Controllers\DepartmentAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
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

        // Lọc theo từ khóa
        if ($request->filled('keyword')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->keyword . '%')
                  ->orWhere('email', 'like', '%' . $request->keyword . '%');
            });
        }

        // Lọc theo trạng thái học (Chờ sau này làm bảng điểm sẽ code query thật vào đây)

        $employees = $query->paginate(15)->withQueryString();

        return Inertia::render('DepartmentAdmin/Employees/Index', [
            'employees' => $employees,
            'filters' => $request->only(['keyword', 'status'])
        ]);
    }
}