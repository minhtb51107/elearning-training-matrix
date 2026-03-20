<?php

namespace App\Http\Controllers\SystemAdmin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseClass;
use App\Models\Department;
use App\Models\Instructor;
use App\Models\User;
use App\Models\ClassEnrollment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;

class CourseClassController extends Controller
{
    public function index(Request $request)
    {
        $query = CourseClass::with(['course', 'instructor', 'department'])->latest();

        if ($request->filled('tab') && $request->tab !== 'all') {
            $query->where('status', $request->tab);
        }
        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->filled('keyword')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->keyword . '%')
                  ->orWhere('code', 'like', '%' . $request->keyword . '%');
            });
        }

        $classes = $query->paginate(15)->withQueryString();
        $courses = Course::select('id', 'name')->orderBy('name')->get();
        $departments = Department::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('SystemAdmin/Classes/Index', [
            'classes' => $classes,
            'courses' => $courses,
            'departments' => $departments,
            'filters' => $request->only(['tab', 'course_id', 'department_id', 'keyword'])
        ]);
    }

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
        return redirect()->route('system.classes.index')->with('success', $message);
    }

public function show($id)
    {
        $courseClass = CourseClass::with([
            'course', 
            'instructor', 
            'department',
            'enrollments.user.department',
            'documents' 
        ])->findOrFail($id);

        $enrolledUserIds = $courseClass->enrollments->pluck('user_id')->toArray();

        $query = User::where('role', 3)->whereNotIn('id', $enrolledUserIds);
        
        if ($courseClass->department_id) {
            $query->where('department_id', $courseClass->department_id);
        }
        
        $availableUsers = $query->with('department')->get();

        return Inertia::render('SystemAdmin/Classes/Show', [
            'courseClass' => $courseClass,
            'availableUsers' => $availableUsers
        ]);
    }

    public function updateStatus(Request $request, CourseClass $courseClass)
    {
        $validated = $request->validate(['status' => 'required|string|in:Nháp,Mở đăng ký,Đang học,Kết thúc']);
        $courseClass->update(['status' => $validated['status']]);
        return redirect()->back()->with('success', 'Đã cập nhật trạng thái lớp học thành: ' . $validated['status']);
    }

    public function addStudents(Request $request, CourseClass $courseClass)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        foreach ($validated['user_ids'] as $userId) {
            ClassEnrollment::firstOrCreate([
                'course_class_id' => $courseClass->id,
                'user_id' => $userId
            ], [
                'status' => 'enrolled' // Lưu đúng chuẩn ENUM của Database
            ]);
        }

        return redirect()->back()->with('success', 'Đã thêm ' . count($validated['user_ids']) . ' học viên vào lớp!');
    }

    public function removeStudent(CourseClass $courseClass, $studentId)
    {
        ClassEnrollment::where('course_class_id', $courseClass->id)
            ->where('user_id', $studentId)
            ->delete();

        return redirect()->back()->with('success', 'Đã gỡ học viên khỏi lớp!');
    }

// =========================================
    // QUẢN LÝ TÀI LIỆU (ĐÃ CHUYỂN SANG S3 - SUPABASE)
    // =========================================
    public function uploadDocument(Request $request, CourseClass $courseClass)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:pdf,link,pptx,doc,video',
            'file' => 'nullable|file|max:20480', // Max 20MB
            'url' => 'nullable|string'
        ]);

        $url = $validated['url'] ?? '';

        // Xử lý upload file vật lý thẳng lên Supabase (S3)
        if ($request->hasFile('file')) {
            // Thay 'public' bằng 's3'
            $path = $request->file('file')->store('documents', 's3');
            
            // Lấy đường dẫn public từ S3 để lưu vào Database
            $url = Storage::disk('s3')->url($path);
        }

        Document::create([
            'course_id' => $courseClass->course_id,
            'course_class_id' => $courseClass->id,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'url' => $url,
        ]);

        return redirect()->back()->with('success', 'Đã tải tài liệu lên Đám mây Supabase thành công!');
    }

    public function deleteDocument(Document $document)
    {
        // Kiểm tra nếu là file tải lên Supabase (trong link có chữ supabase)
        if ($document->type !== 'link' && str_contains($document->url, 'supabase')) {
            // Lấy tên file từ URL (ví dụ: my-file.pdf) và ghép với thư mục documents/
            $path = 'documents/' . basename($document->url);
            
            // Xóa file trên Supabase
            Storage::disk('s3')->delete($path);
        }

        $document->delete();

        return redirect()->back()->with('success', 'Đã xóa tài liệu!');
    }
}