<?php

namespace App\Http\Controllers\SystemAdmin;

use App\Http\Controllers\Controller;
use App\Models\CourseClass;
use App\Models\Course;
use App\Models\Department;
use App\Models\Document;
use App\Enums\RoleEnum; // 👉 Đã thêm dòng này để dùng RoleEnum
use App\Services\SystemAdmin\CourseClassService;
use App\Http\Requests\SystemAdmin\CourseClassRequest;
use App\Http\Requests\SystemAdmin\ClassStatusRequest;
use App\Http\Requests\SystemAdmin\UploadDocumentRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Exports\GradeExport;
use Maatwebsite\Excel\Facades\Excel;

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
            // 👉 ĐÃ SỬA: Load thêm quan hệ 'departments' để giao diện Edit cũng nhận được luồng Data Binding
            'courses' => Course::with('departments')->where('status', '!=', 'Đã kết thúc')->get(),
            'departments' => Department::all(),
            // 👉 ĐÃ SỬA: Gọi Role Enum chuẩn
            'instructors' => \App\Models\User::where('role', '!=', RoleEnum::EMPLOYEE->value)->get()
        ]);
    }

    public function update(CourseClassRequest $request, CourseClass $courseClass)
    {
        $this->classService->updateClass($courseClass, $request->validated());
        return redirect()->route('system.classes.index')->with('success', 'Đã cập nhật lớp học và lịch học thành công!');
    }

    public function destroy(CourseClass $courseClass)
    {
        $courseClass->delete();
        return redirect()->route('system.classes.index')->with('success', 'Đã xóa lớp học!');
    }

    public function exportGrades($id)
    {
        $courseClass = CourseClass::findOrFail($id);
        $fileName = 'DanhSach_ChamDiem_' . $courseClass->code . '.xlsx';
        return Excel::download(new GradeExport($id), $fileName);
    }

    public function importGrades(Request $request, $id)
    {
        return redirect()->back()->with('success', 'Hàm import đang được xây dựng!');
    }

    // API Lấy danh sách nhân viên đủ điều kiện (Toàn công ty)
    public function getEligibleEmployees(Request $request, CourseClass $courseClass)
    {
        // System Admin có thể lọc theo phòng ban nếu muốn, hoặc lấy toàn công ty
        $departmentId = $request->query('department_id'); 
        $employees = $this->classService->getEligibleEmployees($courseClass->id, $departmentId);
        
        return response()->json($employees);
    }

    // Xử lý lưu Chỉ định Đào tạo (Bắt buộc)
    public function assignEmployees(Request $request, CourseClass $courseClass)
    {
        $validated = $request->validate([
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:users,id',
            'deadline' => 'nullable|date|after_or_equal:today',
        ]);

        $this->classService->assignEmployeesToClass(
            $courseClass->id, 
            $validated['employee_ids'], 
            $validated['deadline'] ?? null, 
            auth()->id()
        );

        return redirect()->back()->with('success', 'Đã chỉ định ' . count($validated['employee_ids']) . ' nhân sự tham gia lớp học!');
    }
}