<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Enums\EnrollmentStatusEnum;

class ResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $courseClass = $this->courseClass;
        $course = $courseClass->course ?? null;
        
        $finalAssignment = $course ? $course->assignments->where('type', 'final')->first() : null;
        $submission = $finalAssignment ? $finalAssignment->submissions->first() : null;

        $score = '--';
        $status = 'ĐANG HỌC';
        $cert = '--';
        $actionText = '[Xem]';

        if ($this->status === EnrollmentStatusEnum::COMPLETED->value) {
            $status = 'ĐẠT';
            $score = $submission ? $submission->score : '--';
            $cert = 'Sẵn sàng';
            $actionText = '[Tải]';
        } elseif ($this->status === EnrollmentStatusEnum::FAILED->value) {
            $status = 'KHÔNG ĐẠT';
            $score = $submission ? $submission->score : '--';
            $cert = 'Không cấp';
            $actionText = '[Xem]';
        } elseif ($submission && $submission->status === 'pending') {
            $status = 'CHỜ CHẤM';
            $actionText = '[Xem]';
        }

        return [
            'id' => $this->id,
            'course' => $course ? $course->name : '--',
            'class' => $courseClass->code,
            'date' => $this->updated_at->format('d/m/Y'),
            'score' => $score,
            'status' => $status,
            'cert' => $cert,
            'actionText' => $actionText,
        ];
    }
}