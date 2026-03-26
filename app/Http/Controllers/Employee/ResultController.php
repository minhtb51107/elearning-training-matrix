<?php
namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\ClassEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ResultController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();

        // 1. Lọc lấy các lớp đã Đạt, Không đạt, hoặc đang Chờ chấm điểm
        $query = ClassEnrollment::with([
                'courseClass.course.assignments' => function($q) {
                    $q->where('type', 'final'); // Chỉ lấy bài thi cuối khóa
                },
                'courseClass.course.assignments.submissions' => function($q) use ($userId) {
                    $q->where('user_id', $userId);
                }
            ])
            ->where('user_id', $userId)
            ->where(function($q) use ($userId) {
                $q->whereIn('status', ['completed', 'failed'])
                  ->orWhereHas('courseClass.course.assignments.submissions', function($subQ) use ($userId) {
                      $subQ->where('user_id', $userId)->where('status', 'pending');
                  });
            })
            ->latest();

        // 2. Lọc theo Năm
        if ($request->filled('year') && $request->year !== 'Tất cả') {
            $query->whereYear('updated_at', $request->year);
        }

        // 3. Lọc theo Trạng thái
        if ($request->filled('status') && $request->status !== 'Tất cả') {
            if ($request->status === 'Đạt') {
                $query->where('status', 'completed');
            } elseif ($request->status === 'Không đạt') {
                $query->where('status', 'failed');
            } elseif ($request->status === 'Chờ chấm') {
                $query->where('status', 'in_progress'); // Chưa có kết quả cuối cùng
            }
        }

        // 4. Lọc theo Từ khóa
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->whereHas('courseClass', function($q) use ($keyword) {
                $q->where('code', 'like', "%$keyword%")
                  ->orWhereHas('course', function($q2) use ($keyword) {
                      $q2->where('name', 'like', "%$keyword%");
                  });
            });
        }

        $paginator = $query->paginate(10)->withQueryString();

        // 5. Format dữ liệu cho Vue
        $paginator->getCollection()->transform(function ($enrollment) {
            $courseClass = $enrollment->courseClass;
            $course = $courseClass->course;
            
            $finalAssignment = $course ? $course->assignments->where('type', 'final')->first() : null;
            $submission = $finalAssignment ? $finalAssignment->submissions->first() : null;

            $score = '--';
            $status = 'ĐANG HỌC';
            $cert = '--';
            $actionText = '[Xem]';

            if ($enrollment->status === 'completed') {
                $status = 'ĐẠT';
                $score = $submission ? $submission->score : '--';
                $cert = 'Sẵn sàng';
                $actionText = '[Tải]';
            } elseif ($enrollment->status === 'failed') {
                $status = 'KHÔNG ĐẠT';
                $score = $submission ? $submission->score : '--';
                $cert = 'Không cấp';
                $actionText = '[Xem]';
            } elseif ($submission && $submission->status === 'pending') {
                $status = 'CHỜ CHẤM';
                $actionText = '[Xem]';
            }

            return [
                'id' => $enrollment->id,
                'course' => $course ? $course->name : '--',
                'class' => $courseClass->code,
                'date' => $enrollment->updated_at->format('d/m/Y'),
                'score' => $score,
                'status' => $status,
                'cert' => $cert,
                'actionText' => $actionText,
            ];
        });

        return Inertia::render('Employee/Results/Index', [
            'results' => $paginator,
            'filters' => $request->only(['year', 'status', 'keyword'])
        ]);
    }
}