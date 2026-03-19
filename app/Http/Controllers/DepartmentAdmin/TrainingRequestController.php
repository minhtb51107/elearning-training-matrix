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

        // Xử lý bộ lọc (Filters)
        if ($request->filled('keyword')) {
            // Tìm kiếm trên cả Tên khóa học HOẶC Mã yêu cầu
            $query->where(function($q) use ($request) {
                $q->where('course_name', 'like', '%' . $request->keyword . '%')
                  ->orWhere('code', 'like', '%' . $request->keyword . '%');
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

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
            'expected_duration' => 'required|integer|min:1',
            'notes' => 'nullable|string',
            'action' => 'required|in:draft,pending'
        ]);

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
            'status' => $validated['action']
        ]);

        $message = $validated['action'] === 'draft' ? 'Đã lưu bản nháp!' : 'Đã gửi yêu cầu đào tạo thành công!';
        return redirect()->route('department.requests.index')->with('success', $message);
    }

    // 4. Màn hình Xem chi tiết
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

    // 5. Cập nhật YC (Chỉ cho phép khi đang là Nháp)
    public function update(Request $request, TrainingRequest $trainingRequest)
    {
        if ($trainingRequest->status !== 'draft') {
            return redirect()->back()->with('error', 'Chỉ có thể sửa bản nháp.');
        }

        $validated = $request->validate([
            'course_name' => 'required|string|max:255',
            'target_audience' => 'required|string|max:255',
            'content' => 'required|string',
            'expected_duration' => 'required|integer|min:1',
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