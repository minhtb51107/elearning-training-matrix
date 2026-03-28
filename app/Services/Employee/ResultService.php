<?php

namespace App\Services\Employee;

use App\Models\ClassEnrollment;
use App\Enums\EnrollmentStatusEnum;

class ResultService
{
    public function getMyResults($userId, array $filters)
    {
        $query = ClassEnrollment::with([
                'courseClass.course.assignments' => function($q) {
                    $q->where('type', 'final');
                },
                'courseClass.course.assignments.submissions' => function($q) use ($userId) {
                    $q->where('user_id', $userId);
                }
            ])
            ->where('user_id', $userId)
            ->where(function($q) use ($userId) {
                $q->whereIn('status', [EnrollmentStatusEnum::COMPLETED->value, EnrollmentStatusEnum::FAILED->value])
                  ->orWhereHas('courseClass.course.assignments.submissions', function($subQ) use ($userId) {
                      $subQ->where('user_id', $userId)->where('status', 'pending');
                  });
            })
            ->latest();

        if (!empty($filters['year']) && $filters['year'] !== 'Tất cả') {
            $query->whereYear('updated_at', $filters['year']);
        }

        if (!empty($filters['status']) && $filters['status'] !== 'Tất cả') {
            if ($filters['status'] === 'Đạt') {
                $query->where('status', EnrollmentStatusEnum::COMPLETED->value);
            } elseif ($filters['status'] === 'Không đạt') {
                $query->where('status', EnrollmentStatusEnum::FAILED->value);
            } elseif ($filters['status'] === 'Chờ chấm') {
                $query->where('status', EnrollmentStatusEnum::IN_PROGRESS->value); 
            }
        }

        if (!empty($filters['keyword'])) {
            $keyword = $filters['keyword'];
            $query->whereHas('courseClass', function($q) use ($keyword) {
                $q->where('code', 'like', "%$keyword%")
                  ->orWhereHas('course', function($q2) use ($keyword) {
                      $q2->where('name', 'like', "%$keyword%");
                  });
            });
        }

        return $query->paginate(10)->withQueryString();
    }
}