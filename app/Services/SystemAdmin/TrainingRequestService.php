<?php

namespace App\Services\SystemAdmin;

use App\Models\TrainingRequest;
use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Support\Facades\DB;

class TrainingRequestService
{
    public function getFilteredRequests(array $filters)
    {
        $query = TrainingRequest::with(['department', 'requester'])
            ->where('status', '!=', 'draft')
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
            // Nối chuỗi bằng dấu nháy đơn để bọc an toàn HTML class
            $statusText = $data['status'] === 'approved' ? '<span class="text-green-600">chấp thuận</span>' : '<span class="text-red-600">từ chối</span>';
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
                $req->update(['status' => 'approved', 'updated_at' => now()]);

                $requester = User::find($req->requester_id);
                if ($requester) {
                    // Đã fix: Nối chuỗi bằng dấu nháy đơn (.) thay vì bọc toàn bộ bằng ngoặc kép ("")
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