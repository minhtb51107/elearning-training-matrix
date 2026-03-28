<?php

namespace App\Services\Employee;

use App\Models\ClassSession;
use App\Models\ClassEnrollment;
use Carbon\Carbon;

class MyScheduleService
{
    public function getSchedule($userId, array $filters)
    {
        $enrolledClassIds = ClassEnrollment::where('user_id', $userId)->pluck('course_class_id');

        $query = ClassSession::with(['courseClass.course', 'courseClass.instructor'])
            ->whereIn('course_class_id', $enrolledClassIds);

        $filterTime = $filters['filter_time'] ?? 'Tất cả';
        $now = Carbon::now();

        if ($filterTime === 'Tuần này') {
            $query->whereBetween('date', [
                $now->copy()->startOfWeek()->format('Y-m-d'),
                $now->copy()->endOfWeek()->format('Y-m-d')
            ]);
        } elseif ($filterTime === 'Tháng này') {
            $query->whereMonth('date', $now->month)->whereYear('date', $now->year);
        }

        $query->orderBy('date', 'asc')->orderBy('start_time', 'asc');

        return $query->paginate(10)->withQueryString();
    }
}