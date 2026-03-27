<?php

namespace App\Listeners;

use App\Events\TrainingRequestStatusUpdated;
use App\Models\User;
use App\Enums\RequestStatusEnum;
use App\Notifications\SystemNotification;

class NotifyRequesterOfStatusUpdate
{
    public function handle(TrainingRequestStatusUpdated $event): void
    {
        $requester = User::find($event->trainingRequest->requester_id);
        
        if ($requester) {
            $statusText = $event->status === RequestStatusEnum::APPROVED->value 
                        ? '<span class="text-green-600">chấp thuận</span>' 
                        : '<span class="text-red-600">từ chối</span>';
            
            $requester->notify(new SystemNotification(
                'Kết quả Yêu cầu Đào tạo',
                "Yêu cầu khóa học <strong>{$event->trainingRequest->course_name}</strong> của bạn đã bị {$statusText}.",
                route('department.requests.show', $event->trainingRequest->id)
            ));
        }
    }
}