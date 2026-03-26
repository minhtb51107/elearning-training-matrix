<?php
namespace App\Http\Controllers\DepartmentAdmin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\ClassEnrollment; // <-- Đừng quên import Model này
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

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

    public function show($id)
    {

    $course = Course::with(['departments', 'courseClasses.instructor', 'documents'])->findOrFail($id);

        // 2. Đóng gói dữ liệu Thông tin chung (courseInfo)
        $courseInfo = [
            'code' => $course->code,
            'name' => $course->name,
            'target_audience' => $course->target_audience ?? 'Toàn công ty',
            'format' => $course->format,
            'scope' => $course->departments->pluck('name')->join(', ') ?: 'Không giới hạn',
            'duration' => $course->duration ? $course->duration . ' giờ' : '--',
        ];

        // 3. Đóng gói dữ liệu Danh sách Lớp (classes)
        $classes = $course->courseClasses->map(function ($cls) {
            // Đếm số lượng nhân viên đã đăng ký vào lớp này
            $capacity = ClassEnrollment::where('course_class_id', $cls->id)->count();

            return [
                'code' => $cls->code,
                'name' => $cls->name ?? 'Lớp ' . $cls->code,
                'instructor' => $cls->instructor ? $cls->instructor->name : 'Chưa phân công',
                'time' => $cls->start_date ? date('d/m/Y', strtotime($cls->start_date)) : 'Chưa xếp lịch',
                'capacity' => $capacity
            ];
        });

        // 4. Đóng gói dữ liệu Tài liệu (materials) TỪ DATABASE
        $materials = $course->documents->map(function ($doc) {
            return [
                'name' => $doc->title,
                // Nếu là file thì gọi link storage, nếu là link Drive thì lấy URL thẳng
                'url' => $doc->type === 'file' ? Storage::disk('s3')->url($doc->file_path) : $doc->url,
                'type' => $doc->type // Gửi type sang để Vue nó biết đường đổi Icon
            ];
        });

        // 5. Trả về giao diện Vue với dữ liệu THẬT
        return Inertia::render('DepartmentAdmin/Courses/Show', [
            'courseInfo' => $courseInfo,
            'classes' => $classes,
            'materials' => $materials
        ]);
    }
}