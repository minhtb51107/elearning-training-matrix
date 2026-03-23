<?php
namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseClass;
use App\Models\ClassEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CourseController extends Controller
{
    // 1. Hiển thị chi tiết Khóa học & Danh sách lớp đang mở
    public function show($id)
    {
        $course = Course::with(['courseClasses' => function($query) {
            // Chỉ lấy các lớp chưa kết thúc
            $query->where('status', '!=', 'completed')->with('instructor');
        }])->findOrFail($id);

        $classes = $course->courseClasses->map(function ($cls) {
            // Đếm số người đã đăng ký
            $enrolledCount = ClassEnrollment::where('course_class_id', $cls->id)->count();
            $maxStudents = 30; // Giả sử 1 lớp tối đa 30 người
            $isFull = $enrolledCount >= $maxStudents;

            // Kiểm tra xem User này đã đăng ký lớp này chưa
            $isEnrolled = ClassEnrollment::where('course_class_id', $cls->id)
                            ->where('user_id', Auth::id())
                            ->exists();

            return [
                'id' => $cls->id,
                'name' => $cls->code, // Dùng mã lớp làm tên hiển thị
                'time' => ($cls->start_date ? date('d/m/Y', strtotime($cls->start_date)) : 'Đang cập nhật'),
                'instructor' => $cls->instructor ? $cls->instructor->name : 'Chưa phân công',
                'slot' => $isFull ? 'ĐÃ ĐỦ SỐ LƯỢNG' : ($enrolledCount . '/' . $maxStudents),
                'isFull' => $isFull,
                'isEnrolled' => $isEnrolled
            ];
        });

        return Inertia::render('Employee/Courses/Show', [
            'course' => [
                'id' => $course->id,
                'name' => $course->name,
                'overview' => [
                    '- Mục tiêu: ' . ($course->description ?: 'Đang cập nhật'),
                    '- Đối tượng: ' . $course->target_audience,
                    '- Hình thức: ' . $course->format,
                    '- Thời lượng: ' . $course->duration . 'h'
                ],
                // Giả lập nội dung vì chưa làm module Bài giảng (Lesson)
                'content' => [
                    ['title' => 'Chương 1: Tổng quan', 'time' => '45 min'],
                    ['title' => 'Chương 2: Nội dung chi tiết', 'time' => '90 min'],
                ],
                'classes' => $classes,
            ]
        ]);
    }
    
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // 1. Chỉ hiển thị các khóa học được phân bổ cho Phòng ban của nhân viên này
        $query = Course::whereHas('departments', function($q) use ($user) {
            $q->where('departments.id', $user->department_id);
        })->latest();

        // 2. Lọc theo từ khóa
        if ($request->filled('keyword')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->keyword . '%')
                  ->orWhere('code', 'like', '%' . $request->keyword . '%');
            });
        }

        // 3. Lọc theo Phân loại (Bắt buộc / Tự chọn)
        // Vì DB chưa có cột 'type', ta tạm quy ước: Đối tượng 'Toàn phòng' = Bắt buộc, còn lại = Tự chọn
        if ($request->filled('type') && $request->type !== 'Tất cả') {
            if ($request->type === 'Bắt buộc') {
                $query->where('target_audience', 'Toàn phòng');
            } else {
                $query->where('target_audience', '!=', 'Toàn phòng');
            }
        }

        $paginator = $query->paginate(6)->withQueryString();

        // 4. Format lại dữ liệu khớp 100% với Mock Data của bạn
        $paginator->getCollection()->transform(function ($course) {
            return [
                'id' => $course->id,
                // Lấy 3 chữ cái đầu của tên khóa học làm ảnh cover cho sinh động
                'image' => 'https://placehold.co/600x300/0ea5e9/white?text=' . urlencode(mb_strtoupper(mb_substr($course->name, 0, 3, 'UTF-8'))),
                'title' => $course->name,
                'description' => $course->description ?: 'Chưa có mô tả',
                'date' => $course->created_at->format('m/Y'),
                'type' => $course->target_audience === 'Toàn phòng' ? 'Bắt buộc' : 'Tự chọn'
            ];
        });

        return Inertia::render('Employee/Courses/Index', [
            'courses' => $paginator,
            'filters' => $request->only(['keyword', 'type'])
        ]);
    }

    // 2. Xử lý Đăng ký Lớp học
    public function enroll(Request $request, $classId)
    {
        $userId = Auth::id();

        // Kiểm tra xem đã đăng ký chưa để chống spam
        $exists = ClassEnrollment::where('course_class_id', $classId)
                    ->where('user_id', $userId)
                    ->exists();

        if (!$exists) {
            ClassEnrollment::create([
                'course_class_id' => $classId,
                'user_id' => $userId,
            ]);
        }

        return back()->with('success', 'Đăng ký lớp học thành công! Vui lòng kiểm tra trong Lớp học của tôi.');
    }
}