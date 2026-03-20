<?php

namespace App\Http\Controllers\SystemAdmin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\TrainingRequest;
use App\Models\Department; // Đã thêm Model Department
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class CourseController extends Controller
{
    // 1. Màn hình danh sách Khóa học (BA Spec 4.2.5)
    public function index(Request $request)
    {
        $query = Course::latest();

        // 1. Lọc theo Tab Trạng thái (Tất cả, Chưa có lớp, Đang mở...)
        if ($request->filled('tab') && $request->tab !== 'all') {
            $query->where('status', $request->tab);
        }

        // 2. Lọc theo Thời gian tạo (date)
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        // 3. Lọc theo Phạm vi / Đối tượng (scope)
        if ($request->filled('scope')) {
            $query->where('target_audience', $request->scope);
        }

        // 4. Lọc theo Nguồn tạo (source)
        if ($request->filled('source')) {
            if ($request->source === 'Yêu cầu') {
                $query->whereNull('reason'); // Ghép từ yêu cầu thì reason là null
            } elseif ($request->source === 'Nội bộ') {
                $query->whereNotNull('reason'); // Nội bộ tự tạo thì có reason
            }
        }

        // 5. Lọc theo Tình trạng lớp
        if ($request->filled('status')) {
            if ($request->status === 'Chưa có lớp') {
                $query->where('status', 'Chưa có lớp');
            } elseif ($request->status === 'Có lớp') {
                $query->where('status', '!=', 'Chưa có lớp');
            }
        }

        // 6. Lọc theo Từ khóa (Mã hoặc Tên KH)
        if ($request->filled('keyword')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->keyword . '%')
                  ->orWhere('code', 'like', '%' . $request->keyword . '%');
            });
        }

        $courses = $query->paginate(15)->withQueryString();
        
        // Lấy danh sách phòng ban THẬT từ Database để gửi sang bộ lọc
        $departments = Department::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('SystemAdmin/Courses/Index', [
            'courses' => $courses,
            'departments' => $departments,
            'filters' => $request->only(['tab', 'date', 'scope', 'source', 'status', 'keyword'])
        ]);
    }

    // 2. Màn hình Tạo khóa học mới (BA Spec 4.2.4)
    public function create(Request $request)
    {
        $approvedRequests = TrainingRequest::with('department')
            ->where('status', 'approved')
            ->latest()
            ->get();

        return Inertia::render('SystemAdmin/Courses/Create', [
            'approvedRequests' => $approvedRequests,
            'preselectedRequestId' => $request->query('request_id') 
        ]);
    }

    // 3. Xử lý lưu Khóa học & Cập nhật Yêu cầu
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'target_audience' => 'required|string|max:255',
            'format' => 'required|string|in:Online,Offline,Hybrid',
            'duration' => 'required|integer|min:1',
            'content' => 'nullable|string',
            'description' => 'nullable|string',
            'request_ids' => 'nullable|array',
            'request_ids.*' => 'exists:training_requests,id',
            'reason' => 'required_without:request_ids|nullable|string' 
        ], [
            'reason.required_without' => 'Bạn phải nhập lý do tạo nếu không chọn Yêu cầu đào tạo nào.'
        ]);

        DB::beginTransaction();
        try {
            $count = Course::count() + 1;
            $code = 'KH-' . date('Y') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

            // 1. Tạo Khóa học
            $course = Course::create([
                'code' => $code,
                'name' => $validated['name'],
                'target_audience' => $validated['target_audience'],
                'format' => $validated['format'],
                'duration' => $validated['duration'],
                'content' => $validated['content'],
                'description' => $validated['description'],
                'reason' => $validated['reason'] ?? null,
                'status' => 'Chưa có lớp'
            ]);

            // 2. Xử lý Yêu cầu đào tạo & Phân bổ Phòng ban
            if (!empty($validated['request_ids'])) {
                // Cập nhật trạng thái Yêu cầu
                TrainingRequest::whereIn('id', $validated['request_ids'])->update([
                    'status' => 'processed',
                    'course_id' => $course->id
                ]);

                // Tự động lấy danh sách ID của các Phòng ban đã gửi yêu cầu này
                $deptIds = TrainingRequest::whereIn('id', $validated['request_ids'])
                                ->pluck('department_id')
                                ->filter()
                                ->unique()
                                ->toArray();
                
                // Gắn khóa học cho các phòng ban đó
                if (!empty($deptIds)) {
                    $course->departments()->sync($deptIds);
                }
            } else {
                // Nếu tạo thủ công (Nội bộ), mặc định cho phép TẤT CẢ phòng ban đều thấy khóa học này
                $allDeptIds = Department::pluck('id')->toArray();
                $course->departments()->sync($allDeptIds);
            }

            DB::commit();
            return redirect()->route('system.courses.index')->with('success', 'Đã tạo khóa học thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra khi tạo khóa học: ' . $e->getMessage());
        }
    }
    
    // 4. Màn hình Chi tiết khóa học
    public function show(Course $course)
    {
        return Inertia::render('SystemAdmin/Courses/Show', [
            'course' => $course
        ]);
    }
}