<?php

namespace App\Http\Controllers\DepartmentAdmin;

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
        $user = $request->user();
        
        $query = TrainingRequest::where('department_id', $user->department_id)->latest();

        if ($request->filled('keyword')) {
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

    public function create(Request $request)
    {
        return Inertia::render('DepartmentAdmin/Requests/Create', [
            'department_name' => $request->user()->department->name ?? 'Chưa cập nhật',
            'requester_name' => $request->user()->name
        ]);
    }

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

        $trainingRequest = TrainingRequest::create([
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

        // 👉 BẮN THÔNG BÁO CHO ADMIN HỆ THỐNG
        if ($validated['action'] === 'pending') {
            $systemAdmins = User::where('role', 1)->get();
            foreach ($systemAdmins as $admin) {
                $admin->notify(new SystemNotification(
                    'Yêu cầu đào tạo mới',
                    'Phòng ban <strong>' . ($request->user()->department->name ?? 'N/A') . '</strong> vừa gửi một yêu cầu đào tạo khóa học <strong>' . $validated['course_name'] . '</strong>.',
                    route('system.requests.show', $trainingRequest->id)
                ));
            }
        }

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

        // 👉 BẮN THÔNG BÁO NẾU CHUYỂN TỪ NHÁP SANG GỬI DUYỆT
        if ($validated['action'] === 'pending') {
            $systemAdmins = User::where('role', 1)->get();
            foreach ($systemAdmins as $admin) {
                $admin->notify(new SystemNotification(
                    'Yêu cầu đào tạo mới',
                    'Phòng ban <strong>' . ($request->user()->department->name ?? 'N/A') . '</strong> vừa gửi yêu cầu đào tạo: <strong>' . $validated['course_name'] . '</strong>.',
                    route('system.requests.show', $trainingRequest->id)
                ));
            }
        }

        $message = $validated['action'] === 'draft' ? 'Đã cập nhật bản nháp!' : 'Đã gửi yêu cầu thành công!';
        return redirect()->route('department.requests.index')->with('success', $message);
    }
}