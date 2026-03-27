<?php

namespace App\Services\SystemAdmin;

use App\Models\TrainingRequest;
use App\Models\User;
use App\Notifications\SystemNotification;
use App\Enums\RequestStatusEnum;
use Illuminate\Support\Facades\DB;

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

        $requester = User::find($trainingRequest->requester_id);
        if ($requester) {
            // 👉 Enum
            $statusText = $data['status'] === RequestStatusEnum::APPROVED->value 
                        ? '<span class="text-green-600">chấp thuận</span>' 
                        : '<span class="text-red-600">từ chối</span>';
            $requester->notify(new SystemNotification(
                'Kết quả Yêu cầu Đào tạo',
                'Yêu cầu khóa học <strong>' . $trainingRequest->course_name . '</strong> của bạn đã bị ' . $statusText . '.',
                route('department.requests.show', $trainingRequest->id)
            ));
        }
    }

    public function bulkApproveRequests(array $ids)
    {
        DB::transaction(function () use ($ids) {
            $requests = TrainingRequest::whereIn('id', $ids)->get();

            foreach ($requests as $req) {
                // 👉 Enum
                $req->update(['status' => RequestStatusEnum::APPROVED->value, 'updated_at' => now()]);

                $requester = User::find($req->requester_id);
                if ($requester) {
                    $requester->notify(new SystemNotification(
                        'Kết quả Yêu cầu Đào tạo',
                        'Yêu cầu khóa học <strong>' . $req->course_name . '</strong> của bạn đã được <span class="text-green-600">chấp thuận</span>.',
                        route('department.requests.show', $req->id)
                    ));
                }
            }
        });
    }
}