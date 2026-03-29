<?php
namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Services\Employee\ResultService;
use App\Http\Resources\ResultResource; 
use App\Models\ClassEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf; // 👉 Import DOMPDF

class ResultController extends Controller
{
    protected $resultService;

    public function __construct(ResultService $resultService)
    {
        $this->resultService = $resultService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['year', 'status', 'keyword']);
        $resultsRaw = $this->resultService->getMyResults(Auth::id(), $filters);

        return Inertia::render('Employee/Results/Index', [
            'results' => ResultResource::collection($resultsRaw),
            'filters' => $filters
        ]);
    }

    // 👇 HÀM MỚI: Xử lý tạo và tải file PDF Chứng Chỉ
    public function downloadCertificate($id)
    {
        $enrollment = ClassEnrollment::with(['courseClass.course', 'user'])
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Chỉ cho phép tải nếu trạng thái là ĐẠT
        if ($enrollment->status !== 'completed') {
            abort(403, 'Bạn chưa đủ điều kiện nhận chứng chỉ này.');
        }

        $data = [
            'student_name' => $enrollment->user->name,
            'course_name' => $enrollment->courseClass->course->name,
            'class_code' => $enrollment->courseClass->code,
            'score' => $enrollment->final_score,
            'completed_date' => $enrollment->completed_at ? \Carbon\Carbon::parse($enrollment->completed_at)->format('d/m/Y') : date('d/m/Y'),
            'cert_id' => 'CERT-' . $enrollment->courseClass->code . '-' . str_pad($enrollment->id, 4, '0', STR_PAD_LEFT)
        ];

        // Tạo PDF từ view blade tĩnh
        $pdf = Pdf::loadView('pdf.certificate', $data)
                  ->setPaper('a4', 'landscape'); // Chứng chỉ in ngang
        
        return $pdf->download('Chung_Chi_'.$data['class_code'].'.pdf');
    }
}