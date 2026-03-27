<?php

namespace App\Http\Controllers\SystemAdmin;

use App\Http\Controllers\Controller;
use App\Models\TrainingRequest;
use App\Services\SystemAdmin\TrainingRequestService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Requests\SystemAdmin\ReviewTrainingRequest;

class TrainingRequestController extends Controller
{
    protected $requestService;

    public function __construct(TrainingRequestService $requestService)
    {
        $this->requestService = $requestService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['tab', 'keyword']);

        return Inertia::render('SystemAdmin/Requests/Index', [
            'requests' => $this->requestService->getFilteredRequests($filters),
            'filters' => $filters
        ]);
    }

    public function show(TrainingRequest $trainingRequest)
    {
        if ($trainingRequest->status === 'draft') {
            abort(404, 'Không tìm thấy yêu cầu đào tạo.');
        }

        $trainingRequest->load(['department', 'requester']);

        return Inertia::render('SystemAdmin/Requests/Show', [
            'trainingRequest' => $trainingRequest
        ]);
    }

    public function updateStatus(ReviewTrainingRequest $request, TrainingRequest $trainingRequest)
    {
        if ($trainingRequest->status !== 'pending') {
            return back()->with('error', 'Yêu cầu này đã được xử lý trước đó.');
        }

        $validated = $request->validated();
        $this->requestService->updateStatus($trainingRequest, $validated);

        $message = $validated['status'] === 'approved' ? 'Đã DUYỆT yêu cầu đào tạo thành công!' : 'Đã TỪ CHỐI yêu cầu đào tạo.';
        return redirect()->route('system.requests.index')->with('success', $message);
    }
    
    public function bulkApprove(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:training_requests,id'
        ]);

        $this->requestService->bulkApproveRequests($validated['ids']);
        return back()->with('success', 'Đã duyệt thành công ' . count($validated['ids']) . ' yêu cầu!');
    }
}