<?php
namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Services\Employee\MyScheduleService;
use App\Http\Resources\MyScheduleResource; // 👉 Import Resource của bạn
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

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
            'schedule' => MyScheduleResource::collection($scheduleRaw), // 👉 Bọc Resource ở đây
            'filters' => $filters
        ]);
    }
}