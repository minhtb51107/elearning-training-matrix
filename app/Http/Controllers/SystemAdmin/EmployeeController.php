<?php
namespace App\Http\Controllers\SystemAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Department;
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
        
        // Lấy danh sách phòng ban thật
        $departments = Department::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('SystemAdmin/Employees/Index', [
            'employees' => $employees,
            'departments' => $departments,
            'filters' => $request->only(['keyword', 'department_id', 'position', 'status'])
        ]);
    }
}