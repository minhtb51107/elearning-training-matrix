<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseClass;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CourseClassController extends Controller
{
    public function index(Course $course)
    {
        // Lấy danh sách lớp kèm tên giảng viên
        $classes = $course->classes()->with('instructor')->latest()->get();
        // Lấy danh sách người dùng để gán làm giảng viên
        $instructors = User::select('id', 'name', 'email')->get();

        return Inertia::render('Admin/CourseClasses/Index', [
            'course' => $course,
            'classes' => $classes,
            'instructors' => $instructors
        ]);
    }

    public function store(Request $request, Course $course)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:course_classes,code',
            'name' => 'required|string|max:255',
            'instructor_id' => 'nullable|exists:users,id',
            'instructor_name' => 'nullable|string|max:255',
            'capacity' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:draft,open,in_progress,completed',
        ]);

        $course->classes()->create($validated);
        return redirect()->back()->with('success', 'Tạo lớp học thành công!');
    }

    public function update(Request $request, CourseClass $courseClass)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:course_classes,code,' . $courseClass->id,
            'name' => 'required|string|max:255',
            'instructor_id' => 'nullable|exists:users,id',
            'instructor_name' => 'nullable|string|max:255',
            'capacity' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:draft,open,in_progress,completed',
        ]);

        $courseClass->update($validated);
        return redirect()->back()->with('success', 'Cập nhật lớp học thành công!');
    }

    public function destroy(CourseClass $courseClass)
    {
        $courseClass->delete();
        return redirect()->back()->with('success', 'Đã xóa lớp học!');
    }
}