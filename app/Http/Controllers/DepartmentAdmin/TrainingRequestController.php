<?php

namespace App\Http\Controllers\DepartmentAdmin;

use App\Http\Controllers\Controller;
use App\Models\TrainingRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TrainingRequestController extends Controller
{
    // 1. Màn hình Danh sách YC
    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = TrainingRequest::where('department_id', $user->department_id)->latest();

        // Xử lý bộ lọc (Filters) từ Mockup
        if ($request->filled('keyword')) {
            $query->where('course_name', 'like', '%' . $request->keyword . '%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        // Thêm lọc Từ ngày - Đến ngày ở đây nếu cần...

        $requests = $query->paginate(10)->withQueryString();

        return Inertia::render('DepartmentAdmin/Requests/Index', [
            'requests' => $requests,
            'filters' => $request->only(['keyword', 'status', 'start_date', 'end_date'])
        ]);
    }

    // 2. Màn hình Gửi YC mới
    public function create(Request $request)
    {
        return Inertia::render('DepartmentAdmin/Requests/Create', [
            // Gửi xuống tên Phòng ban và Tên người gửi để làm field readonly theo mockup
            'department_name' => $request->user()->department->name ?? 'Chưa cập nhật',
            'requester_name' => $request->user()->name
        ]);
    }

    // 3. Xử lý lưu YC (Lưu nháp hoặc Gửi duyệt)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_name' => 'required|string|max:255',
            'target_audience' => 'required|string|max:255',
            'content' => 'required|string',
            'expected_duration' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'action' => 'required|in:draft,pending' // Nút bấm là Lưu nháp hay Gửi
        ]);

        // Tạo mã YC tự động (VD: YC-2026-0001)
        $year = date('Y');
        $count = TrainingRequest::whereYear('created_at', $year)->count() + 1;
        $code = 'YC-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

        TrainingRequest::create([
            'code' => $code,
            'department_id' => $request->user()->department_id,
            'requester_id' => $request->user()->id,
            'course_name' => $validated['course_name'],
            'target_audience' => $validated['target_audience'],
            'content' => $validated['content'],
            'expected_duration' => $validated['expected_duration'],
            'notes' => $validated['notes'],
            'status' => $validated['action'] // 'draft' hoặc 'pending'
        ]);

        $message = $validated['action'] === 'draft' ? 'Đã lưu bản nháp!' : 'Đã gửi yêu cầu đào tạo thành công!';
        return redirect()->route('department.requests.index')->with('success', $message);
    }

    // 4. Màn hình Xem chi tiết (Hoặc Sửa nếu đang là Nháp)
    public function show(Request $request, TrainingRequest $trainingRequest)
    {
        // Kiểm tra bảo mật: Không cho xem YC của phòng khác
        if ($trainingRequest->department_id !== $request->user()->department_id) {
            abort(403, 'Bạn không có quyền truy cập yêu cầu này.');
        }

        $trainingRequest->load(['department', 'requester']);

        return Inertia::render('DepartmentAdmin/Requests/Show', [
            'trainingRequest' => $trainingRequest
        ]);
    }

    // 5. Cập nhật YC (Chỉ cho phép khi đang là Nháp)
    public function update(Request $request, TrainingRequest $trainingRequest)
    {
        if ($trainingRequest->status !== 'draft') {
            return redirect()->back()->with('error', 'Chỉ có thể sửa bản nháp.');
        }

        $validated = $request->validate([
            // ... validation giống method store
            'course_name' => 'required|string|max:255',
            'target_audience' => 'required|string|max:255',
            'content' => 'required|string',
            'expected_duration' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'action' => 'required|in:draft,pending'
        ]);

        $trainingRequest->update([
            'course_name' => $validated['course_name'],
            'target_audience' => $validated['target_audience'],
            'content' => $validated['content'],
            'expected_duration' => $validated['expected_duration'],
            'notes' => $validated['notes'],
            'status' => $validated['action']
        ]);

        $message = $validated['action'] === 'draft' ? 'Đã cập nhật bản nháp!' : 'Đã gửi yêu cầu thành công!';
        return redirect()->route('department.requests.index')->with('success', $message);
    }
}