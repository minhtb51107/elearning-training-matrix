<?php

namespace App\Http\Controllers\SystemAdmin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseClass;
use App\Models\Department;
use App\Models\Instructor;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CourseClassController extends Controller
{
    // 1. Màn hình danh sách Lớp học (ĐÃ CODE ĐẦY ĐỦ LOGIC LẤY DỮ LIỆU)
    public function index(Request $request)
    {
        // Lấy danh sách lớp kèm theo thông tin Khóa học, Giảng viên, Phòng ban
        $query = CourseClass::with(['course', 'instructor', 'department'])->latest();

        // Lọc theo Tab
        if ($request->filled('tab') && $request->tab !== 'all') {
            $query->where('status', $request->tab);
        }

        // Lọc theo Khóa học
        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        // Lọc theo Phạm vi (Phòng ban)
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // Lọc theo Từ khóa (Mã lớp hoặc Tên lớp)
        if ($request->filled('keyword')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->keyword . '%')
                  ->orWhere('code', 'like', '%' . $request->keyword . '%');
            });
        }

        // Phân trang và giữ lại params trên URL
        $classes = $query->paginate(15)->withQueryString();

        // Lấy danh sách Khóa học & Phòng ban để nạp vào Dropdown của bộ lọc
        $courses = Course::select('id', 'name')->orderBy('name')->get();
        $departments = Department::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('SystemAdmin/Classes/Index', [
            'classes' => $classes, // ĐÃ TRUYỀN DỮ LIỆU SANG VUE Ở ĐÂY
            'courses' => $courses,
            'departments' => $departments,
            'filters' => $request->only(['tab', 'course_id', 'department_id', 'keyword'])
        ]);
    }

    // 2. Màn hình Tạo Lớp học
    public function create(Request $request)
    {
        $courseId = $request->query('course_id');
        
        $courses = Course::select('id', 'code', 'name', 'duration')
            ->whereIn('status', ['Chưa có lớp', 'Đang triển khai', 'Đang mở'])
            ->get();

        $selectedCourse = $courseId ? Course::find($courseId) : null;
        $departments = Department::select('id', 'name')->orderBy('name')->get();
        $instructors = Instructor::select('id', 'name', 'email')->get();

        return Inertia::render('SystemAdmin/Classes/Create', [
            'courses' => $courses,
            'selectedCourse' => $selectedCourse,
            'departments' => $departments,
            'instructors' => $instructors
        ]);
    }

    // 3. Xử lý lưu Lớp học
    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'name' => 'required|string|max:255',
            'instructor_id' => 'nullable|exists:instructors,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'max_students' => 'required|integer|min:1',
            'format' => 'required|string|in:Online,Offline,Hybrid',
            'department_id' => 'nullable|exists:departments,id',
            'action' => 'required|in:draft,published'
        ]);

        $count = CourseClass::count() + 1;
        $code = 'CLS-' . date('Y') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
        $status = $validated['action'] === 'draft' ? 'Nháp' : 'Mở đăng ký';

        CourseClass::create([
            'course_id' => $validated['course_id'],
            'code' => $code,
            'name' => $validated['name'],
            'instructor_id' => $validated['instructor_id'] ?? null,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'max_students' => $validated['max_students'],
            'format' => $validated['format'],
            'department_id' => $validated['department_id'] ?? null,
            'status' => $status,
        ]);

        $course = Course::find($validated['course_id']);
        if ($course && $course->status === 'Chưa có lớp') {
            $course->update(['status' => 'Đang triển khai']);
        }

        $message = $validated['action'] === 'draft' ? 'Đã lưu nháp lớp học!' : 'Đã tạo lớp học và mở đăng ký thành công!';
        
        // Chuyển về trang danh sách
        return redirect()->route('system.classes.index')->with('success', $message);
    }

    // 4. Màn hình chi tiết Lớp học
    public function show($id)
    {
        // Lấy thông tin lớp học kèm theo Khóa học, Giảng viên, Phòng ban
        $courseClass = CourseClass::with(['course', 'instructor', 'department'])->findOrFail($id);

        return Inertia::render('SystemAdmin/Classes/Show', [
            'courseClass' => $courseClass
        ]);
    }

    // 5. Cập nhật trạng thái lớp học
    public function updateStatus(Request $request, CourseClass $courseClass)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:Nháp,Mở đăng ký,Đang học,Kết thúc'
        ]);

        $courseClass->update(['status' => $validated['status']]);

        return redirect()->back()->with('success', 'Đã cập nhật trạng thái lớp học thành: ' . $validated['status']);
    }
}