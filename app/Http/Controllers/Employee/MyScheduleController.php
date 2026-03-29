<?php
namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Services\Employee\MyScheduleService;
use App\Http\Resources\MyScheduleResource; 
use App\Exports\MyScheduleExport; // 👉 Import Export Class
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class MyScheduleController extends Controller
{
    protected $scheduleService;

    public function __construct(MyScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['filter_time']);
        $scheduleRaw = $this->scheduleService->getSchedule(Auth::id(), $filters);

        return Inertia::render('Employee/MySchedule/Index', [
            'schedule' => MyScheduleResource::collection($scheduleRaw),
            'filters' => $filters
        ]);
    }

    // 👇 HÀM MỚI: Xử lý xuất file Lịch Học
    public function export(Request $request)
    {
        $format = $request->query('format', 'excel');
        $filters = $request->only(['filter_time']);
        
        // Lấy dữ liệu nhưng không phân trang để xuất toàn bộ
        $scheduleData = $this->scheduleService->getSchedule(Auth::id(), $filters, false);

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('pdf.schedule', ['schedules' => $scheduleData])->setPaper('a4', 'landscape');
            return $pdf->download('Lich_Hoc_Cua_Toi.pdf');
        }

        if ($format === 'csv') {
            return Excel::download(new MyScheduleExport($scheduleData), 'Lich_Hoc.csv', \Maatwebsite\Excel\Excel::CSV);
        }

        // Mặc định xuất Excel (.xlsx)
        return Excel::download(new MyScheduleExport($scheduleData), 'Lich_Hoc_Cua_Toi.xlsx');
    }
}