<?php
namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\ClassEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MyClassController extends Controller
{
    public function index(Request $request)
    {
        // 1. Lấy danh sách ghi danh của Nhân viên đang đăng nhập
        $query = ClassEnrollment::with(['courseClass.course'])
            ->where('user_id', Auth::id())
            ->latest();

        // 2. Lọc theo Từ khóa
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->whereHas('courseClass.course', function($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('code', 'like', "%{$keyword}%");
            });
        }

        // 3. Lọc theo Trạng thái
        if ($request->filled('status') && $request->status !== 'Tất cả') {
            $statusMap = [
                'Đang học' => 'in_progress',
                'Hoàn thành' => 'completed',
                'Chưa bắt đầu' => 'pending'
            ];
            if (isset($statusMap[$request->status])) {
                $query->whereHas('courseClass', function($q) use ($statusMap, $request) {
                    $q->where('status', $statusMap[$request->status]);
                });
            }
        }

        // Phân trang
        $paginator = $query->paginate(6)->withQueryString();

        // 4. Format dữ liệu y hệt như cấu trúc MOCKUP của bạn
        $paginator->getCollection()->transform(function ($enrollment) {
            $class = $enrollment->courseClass;
            $course = $class->course;

            // Khởi tạo mặc định
            $statusText = 'Đã đăng ký - Chưa bắt đầu';
            $progress = null;
            $progressText = '';
            $btn = 'BẮT ĐẦU HỌC';
            $isFailed = false;

            // Xử lý trạng thái (Vì bảng điểm chưa code nên ta tạm giả lập Tiến độ)
            if ($class->status === 'in_progress') {
                $statusText = 'Đang học';
                $progress = 50; // Giả lập tiến độ 50%
                $progressText = '50%';
                $btn = 'HỌC TIẾP';
            } elseif ($class->status === 'completed') {
                $statusText = 'Hoàn thành';
                $progress = 100;
                $progressText = 'PASSED';
                $btn = 'XEM KẾT QUẢ';
            }

            // Xử lý ngày tháng
            $startDate = $class->start_date ? date('m/Y', strtotime($class->start_date)) : 'N/A';
            $endDate = $class->end_date ? date('m/Y', strtotime($class->end_date)) : 'N/A';

            return [
                'id' => $class->id,
                'title' => $course->name ?? 'Không tên',
                'description' => $course->description,
                'date' => $startDate . ' - ' . $endDate,
                'statusText' => $statusText,
                'progress' => $progress,
                'progressText' => $progressText,
                'btn' => $btn,
                'isFailed' => $isFailed,
            ];
        });

        return Inertia::render('Employee/MyClasses/Index', [
            'classes' => $paginator,
            'filters' => $request->only(['keyword', 'status'])
        ]);
    }
}