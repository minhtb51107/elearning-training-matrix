<?php

namespace App\Http\Controllers\SystemAdmin;

use App\Http\Controllers\Controller;
use App\Models\TrainingRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TrainingRequestController extends Controller
{
    // 1. Màn hình danh sách duyệt Yêu cầu (BA Spec 4.2.2)
    public function index(Request $request)
    {
        // Admin hệ thống xem được tất cả yêu cầu, NGOẠI TRỪ các bản nháp (draft) của phòng ban
        $query = TrainingRequest::with(['department', 'requester'])
            ->where('status', '!=', 'draft')
            ->latest();

        // Lọc theo Tab trạng thái (pending, approved, rejected, processed)
        if ($request->filled('tab') && $request->tab !== 'all') {
            $query->where('status', $request->tab);
        }

        // Lọc theo keyword (Mã YC hoặc Tên khóa)
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

    // 2. Màn hình chi tiết duyệt Yêu cầu (BA Spec 4.2.3)
    public function show(TrainingRequest $trainingRequest)
    {
        // Chặn không cho xem bản nháp của phòng ban
        if ($trainingRequest->status === 'draft') {
            abort(404, 'Không tìm thấy yêu cầu đào tạo.');
        }

        $trainingRequest->load(['department', 'requester']);

        return Inertia::render('SystemAdmin/Requests/Show', [
            'trainingRequest' => $trainingRequest
        ]);
    }

    // 3. Xử lý logic Duyệt / Từ chối
    public function updateStatus(Request $request, TrainingRequest $trainingRequest)
    {
        // Chỉ cho phép xử lý khi YC đang ở trạng thái "Chờ duyệt" (pending)
        if ($trainingRequest->status !== 'pending') {
            return back()->with('error', 'Yêu cầu này đã được xử lý trước đó.');
        }

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            // Nếu chọn 'rejected' (Từ chối) thì bắt buộc phải nhập lý do
            'hr_feedback' => 'required_if:status,rejected|nullable|string' 
        ], [
            'hr_feedback.required_if' => 'Bạn phải nhập lý do khi từ chối yêu cầu đào tạo này.'
        ]);

        $trainingRequest->update([
            'status' => $validated['status'],
            'hr_feedback' => $validated['hr_feedback']
        ]);

        $message = $validated['status'] === 'approved' 
            ? 'Đã DUYỆT yêu cầu đào tạo thành công!' 
            : 'Đã TỪ CHỐI yêu cầu đào tạo.';

        return redirect()->route('system.requests.index')->with('success', $message);
    }
}