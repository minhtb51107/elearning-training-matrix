<?php

namespace App\Http\Controllers\SystemAdmin;

use App\Http\Controllers\Controller;
use App\Services\SystemAdmin\GradeService;
use App\Http\Requests\SystemAdmin\GradeSubmissionRequest;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel; // 👉 Thêm dòng này
use App\Exports\GradeExport; // 👉 Thêm dòng này
use App\Imports\GradeImport; // 👉 Thêm dòng này

class GradeController extends Controller
{
    protected $gradeService;

    public function __construct(GradeService $gradeService)
    {
        $this->gradeService = $gradeService;
    }

    public function index()
    {
        return Inertia::render('SystemAdmin/Grades/Index', [
            'submissions' => $this->gradeService->getPendingSubmissions()
        ]);
    }

    public function show($id)
    {
        return Inertia::render('SystemAdmin/Grades/Show', [
            'submission' => $this->gradeService->getSubmissionDetail($id)
        ]);
    }

    public function update(GradeSubmissionRequest $request, $id)
    {
        $this->gradeService->gradeSubmission($id, $request->validated(), auth()->id());
        return redirect()->route('system.grades.index')->with('success', 'Đã chấm điểm thành công!');
    }

    // ==========================================
    // CÁC HÀM XỬ LÝ EXCEL MỚI
    // ==========================================

    public function export(Request $request)
    {
        $request->validate([
            'assignment_id' => 'required|exists:assignments,id',
            'course_class_id' => 'required|exists:course_classes,id'
        ]);

        $fileName = 'BangDiem_Lop_' . $request->course_class_id . '_' . date('dmY') . '.xlsx';
        
        return Excel::download(
            new GradeExport($request->assignment_id, $request->course_class_id), 
            $fileName
        );
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:5120' // Tối đa 5MB
        ]);

        try {
            Excel::import(new GradeImport($this->gradeService), $request->file('file'));
            return redirect()->back()->with('success', 'Nhập điểm từ file Excel thành công! Hệ thống đang tiến hành thông báo đến học viên.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi khi đọc file Excel. Vui lòng kiểm tra lại định dạng Template.');
        }
    }
}