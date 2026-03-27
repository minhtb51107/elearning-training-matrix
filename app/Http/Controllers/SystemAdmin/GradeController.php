<?php

namespace App\Http\Controllers\SystemAdmin;

use App\Http\Controllers\Controller;
use App\Services\SystemAdmin\GradeService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Requests\SystemAdmin\GradeSubmissionRequest;

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
}