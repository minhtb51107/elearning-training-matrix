<?php

namespace App\Http\Controllers\SystemAdmin;

use App\Http\Controllers\Controller;
use App\Models\CourseClass;
use App\Models\Course;
use App\Models\Department;
use App\Models\Document;
use App\Services\SystemAdmin\CourseClassService;
use App\Http\Requests\SystemAdmin\CourseClassRequest;
use App\Http\Requests\SystemAdmin\ClassStatusRequest;
use App\Http\Requests\SystemAdmin\UploadDocumentRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CourseClassController extends Controller
{
    protected $classService;

    public function __construct(CourseClassService $classService)
    {
        $this->classService = $classService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['tab', 'course_id', 'department_id', 'keyword']);
        
        return Inertia::render('SystemAdmin/Classes/Index', [
            'classes' => $this->classService->getFilteredClasses($filters),
            'courses' => Course::select('id', 'name')->orderBy('name')->get(),
            'departments' => Department::select('id', 'name')->orderBy('name')->get(),
            'filters' => $filters
        ]);
    }

    public function create(Request $request)
    {
        return Inertia::render('SystemAdmin/Classes/Create', $this->classService->getCreateData($request->query('course_id')));
    }

    public function store(CourseClassRequest $request)
    {
        $validated = $request->validated();
        $this->classService->createClass($validated);

        $message = $validated['action'] === 'draft' ? 'Đã lưu nháp lớp học!' : 'Đã tạo lớp học và mở đăng ký thành công!';
        return redirect()->route('system.classes.index')->with('success', $message);
    }

    public function show($id)
    {
        return Inertia::render('SystemAdmin/Classes/Show', $this->classService->getClassDetails($id));
    }

    public function updateStatus(ClassStatusRequest $request, CourseClass $courseClass)
    {
        $this->classService->updateStatus($courseClass, $request->validated()['status']);
        return redirect()->back()->with('success', 'Đã cập nhật trạng thái lớp học thành: ' . $request->validated()['status']);
    }

    public function addStudents(Request $request, CourseClass $courseClass)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        $this->classService->addStudents($courseClass, $validated['user_ids']);
        return redirect()->back()->with('success', 'Đã thêm ' . count($validated['user_ids']) . ' học viên vào lớp!');
    }

    public function removeStudent(CourseClass $courseClass, $studentId)
    {
        $this->classService->removeStudent($courseClass, $studentId);
        return redirect()->back()->with('success', 'Đã gỡ học viên khỏi lớp!');
    }

    public function uploadDocument(UploadDocumentRequest $request, CourseClass $courseClass)
    {
        $this->classService->uploadDocument($courseClass, $request->validated(), $request->file('file'));
        return redirect()->back()->with('success', 'Đã tải tài liệu lên Đám mây Supabase thành công!');
    }

    public function deleteDocument(Document $document)
    {
        $this->classService->deleteDocument($document);
        return redirect()->back()->with('success', 'Đã xóa tài liệu!');
    }

    public function edit(CourseClass $courseClass)
    {
        $courseClass->load('course', 'instructor', 'department', 'sessions');
        return Inertia::render('SystemAdmin/Classes/Edit', [
            'courseClass' => $courseClass,
            'courses' => Course::where('status', '!=', 'Đã kết thúc')->get(),
            'departments' => Department::all(),
            // 👉 Sửa 'role', 3 thành RoleEnum
            'instructors' => \App\Models\User::where('role', '!=', RoleEnum::EMPLOYEE->value)->get()
        ]);
    }

    public function update(CourseClassRequest $request, CourseClass $courseClass)
    {
        // Nhờ Form Request, hàm update giờ chỉ còn đúng 2 dòng siêu mỏng
        $this->classService->updateClass($courseClass, $request->validated());
        return redirect()->route('system.classes.index')->with('success', 'Đã cập nhật lớp học và lịch học thành công!');
    }

    public function destroy(CourseClass $courseClass)
    {
        $courseClass->delete();
        return redirect()->route('system.classes.index')->with('success', 'Đã xóa lớp học!');
    }
}