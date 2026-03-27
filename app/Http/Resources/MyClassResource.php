<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Enums\EnrollmentStatusEnum;
use Carbon\Carbon;

class MyClassResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $cls = $this->courseClass;
        $course = $cls->course ?? null;
        
        $dateStr = 'Chưa xác định';
        if ($cls->start_date && $cls->end_date) {
            $dateStr = Carbon::parse($cls->start_date)->format('m/Y') . ' - ' . Carbon::parse($cls->end_date)->format('m/Y');
        }

        $statusMap = [
            EnrollmentStatusEnum::ENROLLED->value => 'Đã đăng ký - Chưa bắt đầu',
            EnrollmentStatusEnum::IN_PROGRESS->value => 'Đang học',
            EnrollmentStatusEnum::COMPLETED->value => 'Đã hoàn thành',
            EnrollmentStatusEnum::FAILED->value => 'Chưa đạt'
        ];
        
        $progress = $this->progress_percent ?? 0;

        return [
            'id' => $cls->id,
            'title' => $cls->code,
            'course_name' => $course ? $course->name : '--',
            'description' => $cls->name,
            'date' => $dateStr,
            'statusText' => $statusMap[$this->status] ?? 'Chưa xác định',
            'progress' => $progress,
            'progressText' => $progress . '%',
            'isFailed' => $this->status === EnrollmentStatusEnum::FAILED->value,
            'btn' => in_array($this->status, [EnrollmentStatusEnum::COMPLETED->value, EnrollmentStatusEnum::FAILED->value]) ? 'Xem kết quả' : 'Bắt đầu học'
        ];
    }
}