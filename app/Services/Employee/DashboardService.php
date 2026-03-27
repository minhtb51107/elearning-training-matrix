<?php

namespace App\Services\Employee;

use App\Models\ClassEnrollment;
use App\Enums\EnrollmentStatusEnum;
use Carbon\Carbon;

class DashboardService
{
    public function getDashboardData($user): array
    {
        $user->load('department');
        $today = Carbon::today();

        $activeStatuses = [EnrollmentStatusEnum::ENROLLED->value, EnrollmentStatusEnum::IN_PROGRESS->value];

        $learningCount = ClassEnrollment::where('user_id', $user->id)
            ->whereIn('status', $activeStatuses)
            ->count();

        $upcomingCount = ClassEnrollment::where('user_id', $user->id)
            ->whereIn('status', $activeStatuses)
            ->whereHas('courseClass', fn($q) => $q->whereDate('start_date', '>=', $today))
            ->count();

        $upcomingClasses = ClassEnrollment::with(['courseClass.instructor', 'courseClass.sessions'])
            ->where('user_id', $user->id)
            ->whereIn('status', $activeStatuses)
            ->whereHas('courseClass', fn($q) => $q->whereDate('start_date', '>=', $today))
            ->get()
            ->sortBy(fn($enrollment) => $enrollment->courseClass->start_date)
            ->take(3)
            ->values()
            ->map(function ($enrollment) use ($today) {
                $cls = $enrollment->courseClass;
                $startDate = Carbon::parse($cls->start_date);
                
                if ($startDate->isToday()) {
                    $dateStr = 'Hôm nay - ' . $startDate->format('d/m/Y');
                    $canJoin = true;
                } elseif ($startDate->isTomorrow()) {
                    $dateStr = 'Ngày mai - ' . $startDate->format('d/m/Y');
                    $canJoin = true; 
                } else {
                    $dateStr = $startDate->format('d/m/Y');
                    $canJoin = false;
                }

                $firstSession = $cls->sessions->sortBy('date')->first();
                $timeStr = $firstSession ? Carbon::parse($firstSession->start_time)->format('H:i') . ' - ' . Carbon::parse($firstSession->end_time)->format('H:i') : 'Theo lịch phân công';
                $formatStr = $firstSession ? $firstSession->format . ($firstSession->room ? ' - Phòng ' . $firstSession->room : '') : $cls->format;

                return [
                    'id' => $cls->id,
                    'date' => $dateStr,
                    'title' => $cls->name,
                    'time' => $timeStr,
                    'format' => $formatStr,
                    'instructor' => $cls->instructor ? $cls->instructor->name : '--',
                    'action' => $canJoin ? 'Vào lớp học' : 'Sắp diễn ra',
                    'canJoin' => $canJoin,
                    'url' => route('employee.my-classes.show', $cls->id)
                ];
            });

        $inProgressClasses = ClassEnrollment::with(['courseClass.course'])
            ->where('user_id', $user->id)
            ->whereIn('status', $activeStatuses)
            ->latest('updated_at')
            ->take(3)
            ->get()
            ->map(function ($enrollment) {
                $cls = $enrollment->courseClass;
                return [
                    'id' => $cls->id,
                    'course' => $cls->course ? $cls->course->name : '--',
                    'class' => $cls->name,
                    'progress' => $enrollment->progress_percent ?? 0,
                    'url' => route('employee.my-classes.show', $cls->id)
                ];
            });

        return [
            'employeeStats' => [
                'learning' => $learningCount,
                'upcoming' => $upcomingCount,
                'department' => $user->department ? $user->department->name : '--',
            ],
            'upcomingClasses' => $upcomingClasses,
            'inProgressClasses' => $inProgressClasses
        ];
    }
}