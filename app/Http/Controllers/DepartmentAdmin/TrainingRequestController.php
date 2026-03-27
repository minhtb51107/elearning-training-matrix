<?php

namespace App\Http\Controllers\DepartmentAdmin;

use App\Http\Controllers\Controller;
use App\Models\TrainingRequest;
use App\Services\DepartmentAdmin\TrainingRequestService;
use App\Http\Requests\DepartmentAdmin\StoreTrainingRequest; // 👉 Import Form Request vừa tạo
use Illuminate\Http\Request;
use Inertia\Inertia;

class TrainingRequestController extends Controller
{
    protected $requestService;

    public function __construct(TrainingRequestService $requestService)
    {
        $this->requestService = $requestService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['keyword', 'status', 'start_date', 'end_date']);
        $requests = $this->requestService->getFilteredRequests($request->user()->department_id, $filters);

        return Inertia::render('DepartmentAdmin/Requests/Index', [
            'requests' => $requests,
            'filters' => $filters
        ]);
    }

    public function create(Request $request)
    {
        return Inertia::render('DepartmentAdmin/Requests/Create', [
            'department_name' => $request->user()->department->name ?? 'Chưa cập nhật',
            'requester_name' => $request->user()->name
        ]);
    }

    // 👉 Đổi Request thành StoreTrainingRequest
    public function store(StoreTrainingRequest $request) 
    {
        // Lấy dữ liệu đã được làm sạch và validate thành công
        $validated = $request->validated(); 
        
        $this->requestService->createRequest($validated, $request->user());

        $message = $validated['action'] === 'draft' ? 'Đã lưu bản nháp!' : 'Đã gửi yêu cầu đào tạo thành công!';
        return redirect()->route('department.requests.index')->with('success', $message);
    }

    public function show(Request $request, TrainingRequest $trainingRequest)
    {
        if ($trainingRequest->department_id !== $request->user()->department_id) {
            abort(403, 'Bạn không có quyền truy cập yêu cầu này.');
        }

        $trainingRequest->load(['department', 'requester']);

        return Inertia::render('DepartmentAdmin/Requests/Show', [
            'trainingRequest' => $trainingRequest
        ]);
    }

    // 👉 Đổi Request thành StoreTrainingRequest để tái sử dụng lại luật kiểm tra
    public function update(StoreTrainingRequest $request, TrainingRequest $trainingRequest)
    {
        if ($trainingRequest->status !== 'draft') {
            return redirect()->back()->with('error', 'Chỉ có thể sửa bản nháp.');
        }

        $validated = $request->validated();

        $this->requestService->updateRequest($trainingRequest, $validated, $request->user());

        $message = $validated['action'] === 'draft' ? 'Đã cập nhật bản nháp!' : 'Đã gửi yêu cầu thành công!';
        return redirect()->route('department.requests.index')->with('success', $message);
    }
}