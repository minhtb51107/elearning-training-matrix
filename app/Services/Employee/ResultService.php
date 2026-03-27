<?php

namespace App\Services\Employee;

use App\Models\ClassEnrollment;

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
                $q->whereIn('status', ['completed', 'failed'])
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
                $query->where('status', 'completed');
            } elseif ($filters['status'] === 'Không đạt') {
                $query->where('status', 'failed');
            } elseif ($filters['status'] === 'Chờ chấm') {
                $query->where('status', 'in_progress'); 
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

        $paginator = $query->paginate(10)->withQueryString();

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

        return $paginator;
    }
}