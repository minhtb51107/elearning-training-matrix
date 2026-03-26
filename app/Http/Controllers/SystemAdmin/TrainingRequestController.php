<?php

namespace App\Http\Controllers\SystemAdmin;

use App\Http\Controllers\Controller;
use App\Models\TrainingRequest;
use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TrainingRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = TrainingRequest::with(['department', 'requester'])
            ->where('status', '!=', 'draft')
            ->latest();

        if ($request->filled('tab') && $request->tab !== 'all') {
            $query->where('status', $request->tab);
        }

        if ($request->filled('keyword')) {
            $query->where(function($q) use ($request) {
                $q->where('course_name', 'like', '%' . $request->keyword . '%')
                  ->orWhere('code', 'like', '%' . $request->keyword . '%');
            });
        }

        $requests = $query->paginate(15)->withQueryString();

        return Inertia::render('SystemAdmin/Requests/Index', [
            'requests' => $requests,
            'filters' => $request->only(['tab', 'keyword'])
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

    public function updateStatus(Request $request, TrainingRequest $trainingRequest)
    {
        if ($trainingRequest->status !== 'pending') {
            return back()->with('error', 'Yêu cầu này đã được xử lý trước đó.');
        }

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'hr_feedback' => 'required_if:status,rejected|nullable|string' 
        ], [
            'hr_feedback.required_if' => 'Bạn phải nhập lý do khi từ chối yêu cầu đào tạo này.'
        ]);

        $trainingRequest->update([
            'status' => $validated['status'],
            'hr_feedback' => $validated['hr_feedback']
        ]);

        // 👉 BẮN THÔNG BÁO CHO TRƯỞNG PHÒNG ĐÃ TẠO YÊU CẦU NÀY
        $requester = User::find($trainingRequest->requester_id);
        if ($requester) {
            $statusText = $validated['status'] === 'approved' ? '<span class="text-green-600">chấp thuận</span>' : '<span class="text-red-600">từ chối</span>';
            $requester->notify(new SystemNotification(
                'Kết quả Yêu cầu Đào tạo',
                'Yêu cầu khóa học <strong>' . $trainingRequest->course_name . '</strong> của bạn đã bị ' . $statusText . '.',
                route('department.requests.show', $trainingRequest->id)
            ));
        }

        $message = $validated['status'] === 'approved' ? 'Đã DUYỆT yêu cầu đào tạo thành công!' : 'Đã TỪ CHỐI yêu cầu đào tạo.';
        return redirect()->route('system.requests.index')->with('success', $message);
    }
    
    public function bulkApprove(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:training_requests,id'
        ]);

        $requests = TrainingRequest::whereIn('id', $request->ids)->get();

        foreach ($requests as $req) {
            $req->update([
                'status' => 'approved',
                'updated_at' => now()
            ]);

            // 👉 BẮN THÔNG BÁO CHO TỪNG TRƯỞNG PHÒNG CỦA CÁC YÊU CẦU ĐƯỢC DUYỆT HÀNG LOẠT
            $requester = User::find($req->requester_id);
            if ($requester) {
                $requester->notify(new SystemNotification(
                    'Kết quả Yêu cầu Đào tạo',
                    'Yêu cầu khóa học <strong>' . $req->course_name . '</strong> của bạn đã được <span class="text-green-600">chấp thuận</span>.',
                    route('department.requests.show', $req->id)
                ));
            }
        }

        return back()->with('success', 'Đã duyệt thành công ' . count($request->ids) . ' yêu cầu!');
    }
}