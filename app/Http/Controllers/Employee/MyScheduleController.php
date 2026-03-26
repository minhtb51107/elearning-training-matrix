<?php
namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\ClassSession;
use App\Models\ClassEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class MyScheduleController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        
        // 1. Lấy ID các lớp mà nhân viên này đang theo học
        $enrolledClassIds = ClassEnrollment::where('user_id', $userId)
            ->pluck('course_class_id');

        // 2. Lấy danh sách các Buổi học thuộc các lớp đó
        $query = ClassSession::with(['courseClass.course', 'courseClass.instructor'])
            ->whereIn('course_class_id', $enrolledClassIds);

        // 3. Lọc theo thời gian (Tuần này / Tháng này / Tất cả)
        $filterTime = $request->input('filter_time', 'Tất cả');
        $now = Carbon::now();

        if ($filterTime === 'Tuần này') {
            $query->whereBetween('date', [
                $now->copy()->startOfWeek()->format('Y-m-d'),
                $now->copy()->endOfWeek()->format('Y-m-d')
            ]);
        } elseif ($filterTime === 'Tháng này') {
            $query->whereMonth('date', $now->month)
                  ->whereYear('date', $now->year);
        }

        // Sắp xếp ngày gần nhất lên trước
        $query->orderBy('date', 'asc')->orderBy('start_time', 'asc');

        $paginator = $query->paginate(10)->withQueryString();

        // 4. Định dạng dữ liệu cho Vue
        $paginator->getCollection()->transform(function ($session) {
            $courseClass = $session->courseClass;
            $course = $courseClass ? $courseClass->course : null;
            $instructor = $courseClass ? $courseClass->instructor : null;
            
            // Format ngày: T2 19/01/26
            $carbonDate = Carbon::parse($session->date);
            $dayOfWeekMap = [0 => 'CN', 1 => 'T2', 2 => 'T3', 3 => 'T4', 4 => 'T5', 5 => 'T6', 6 => 'T7'];
            $dayStr = $dayOfWeekMap[$carbonDate->dayOfWeek];
            $formattedDate = $dayStr . ' ' . $carbonDate->format('d/m/y');

            $startTime = Carbon::parse($session->start_time)->format('H:i');
            $endTime = Carbon::parse($session->end_time)->format('H:i');

            return [
                'id' => $session->id,
                'title' => $session->title,
                'date' => $formattedDate,
                'class' => $courseClass ? $courseClass->name : '--',
                'course' => $course ? $course->name : '--',
                'instructor' => $instructor ? $instructor->name : '--',
                'format' => $courseClass ? $courseClass->format : '--', // Lấy format của lớp học
                'time' => $startTime . '-' . $endTime,
                'status' => $courseClass ? $courseClass->status : '--',
                'link' => $session->meet_link, // Cột link online
                'room' => $session->room, // Cột phòng offline
            ];
        });

        return Inertia::render('Employee/MySchedule/Index', [
            'schedule' => $paginator,
            'filters' => $request->only(['filter_time'])
        ]);
    }
}