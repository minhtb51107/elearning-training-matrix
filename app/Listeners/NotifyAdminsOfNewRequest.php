<?php

namespace App\Listeners;

use App\Events\TrainingRequestSubmitted;
use App\Models\User;
use App\Enums\RoleEnum;
use App\Notifications\SystemNotification;

class NotifyAdminsOfNewRequest
{
    public function handle(TrainingRequestSubmitted $event): void
    {
        $departmentName = $event->requester->department->name ?? 'N/A';
        $systemAdmins = User::where('role', RoleEnum::SYSTEM_ADMIN->value)->get();
        
        foreach ($systemAdmins as $admin) {
            $admin->notify(new SystemNotification(
                'Yêu cầu đào tạo mới',
                "Phòng ban <strong>{$departmentName}</strong> vừa gửi yêu cầu đào tạo: <strong>{$event->trainingRequest->course_name}</strong>.",
                route('system.requests.show', $event->trainingRequest->id)
            ));
        }
    }
}