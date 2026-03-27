<?php

namespace App\Services\SystemAdmin;

use App\Models\TrainingRequest;
use App\Models\User;
use App\Notifications\SystemNotification;
use App\Enums\RequestStatusEnum;
use Illuminate\Support\Facades\DB;
use App\Events\TrainingRequestStatusUpdated;

class TrainingRequestService
{
    public function getFilteredRequests(array $filters)
    {
        // 👉 Enum
        $query = TrainingRequest::with(['department', 'requester'])
            ->where('status', '!=', RequestStatusEnum::DRAFT->value)
            ->latest();

        if (!empty($filters['tab']) && $filters['tab'] !== 'all') {
            $query->where('status', $filters['tab']);
        }

        if (!empty($filters['keyword'])) {
            $query->where(function($q) use ($filters) {
                $q->where('course_name', 'like', '%' . $filters['keyword'] . '%')
                  ->orWhere('code', 'like', '%' . $filters['keyword'] . '%');
            });
        }

        return $query->paginate(15)->withQueryString();
    }

public function updateStatus(TrainingRequest $trainingRequest, array $data)
    {
        $trainingRequest->update([
            'status' => $data['status'],
            'hr_feedback' => $data['hr_feedback']
        ]);

        // 👉 Bắn sự kiện (Fire Event)
        event(new TrainingRequestStatusUpdated($trainingRequest, $data['status']));
    }

    public function bulkApproveRequests(array $ids)
    {
        DB::transaction(function () use ($ids) {
            $requests = TrainingRequest::whereIn('id', $ids)->get();

            foreach ($requests as $req) {
                $req->update(['status' => RequestStatusEnum::APPROVED->value, 'updated_at' => now()]);

                // 👉 Bắn sự kiện (Fire Event)
                event(new TrainingRequestStatusUpdated($req, RequestStatusEnum::APPROVED->value));
            }
        });
    }
}