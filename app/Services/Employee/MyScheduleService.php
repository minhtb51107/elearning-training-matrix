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

        $paginator = $query->paginate(10)->withQueryString();

        $paginator->getCollection()->transform(function ($session) {
            $courseClass = $session->courseClass;
            $course = $courseClass ? $courseClass->course : null;
            $instructor = $courseClass ? $courseClass->instructor : null;
            
            $carbonDate = Carbon::parse($session->date);
            $dayOfWeekMap = [0 => 'CN', 1 => 'T2', 2 => 'T3', 3 => 'T4', 4 => 'T5', 5 => 'T6', 6 => 'T7'];
            $dayStr = $dayOfWeekMap[$carbonDate->dayOfWeek];
            
            return [
                'id' => $session->id,
                'title' => $session->title,
                'date' => $dayStr . ' ' . $carbonDate->format('d/m/y'),
                'class' => $courseClass ? $courseClass->name : '--',
                'course' => $course ? $course->name : '--',
                'instructor' => $instructor ? $instructor->name : '--',
                'format' => $courseClass ? $courseClass->format : '--', 
                'time' => Carbon::parse($session->start_time)->format('H:i') . '-' . Carbon::parse($session->end_time)->format('H:i'),
                'status' => $courseClass ? $courseClass->status : '--',
                'link' => $session->meet_link, 
                'room' => $session->room, 
            ];
        });

        return $paginator;
    }
}