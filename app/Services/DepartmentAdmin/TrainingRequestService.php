<?php

namespace App\Services\DepartmentAdmin;

use App\Models\TrainingRequest;
use App\Models\User;
use App\Notifications\SystemNotification;

class TrainingRequestService
{
    public function getFilteredRequests($departmentId, array $filters)
    {
        $query = TrainingRequest::where('department_id', $departmentId)->latest();

        if (!empty($filters['keyword'])) {
            $query->where(function($q) use ($filters) {
                $q->where('course_name', 'like', '%' . $filters['keyword'] . '%')
                  ->orWhere('code', 'like', '%' . $filters['keyword'] . '%');
            });
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        return $query->paginate(10)->withQueryString();
    }

    public function createRequest(array $data, $user)
    {
        $year = date('Y');
        $count = TrainingRequest::whereYear('created_at', $year)->count() + 1;
        $code = 'YC-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

        $trainingRequest = TrainingRequest::create([
            'code' => $code,
            'department_id' => $user->department_id,
            'requester_id' => $user->id,
            'course_name' => $data['course_name'],
            'target_audience' => $data['target_audience'],
            'content' => $data['content'],
            'expected_duration' => $data['expected_duration'],
            'notes' => $data['notes'],
            'status' => $data['action']
        ]);

        if ($data['action'] === 'pending') {
            $this->notifySystemAdmins($user, $trainingRequest);
        }

        return $trainingRequest;
    }

    public function updateRequest(TrainingRequest $trainingRequest, array $data, $user)
    {
        $trainingRequest->update([
            'course_name' => $data['course_name'],
            'target_audience' => $data['target_audience'],
            'content' => $data['content'],
            'expected_duration' => $data['expected_duration'],
            'notes' => $data['notes'],
            'status' => $data['action']
        ]);

        if ($data['action'] === 'pending') {
            $this->notifySystemAdmins($user, $trainingRequest);
        }

        return $trainingRequest;
    }

    private function notifySystemAdmins($user, $trainingRequest)
    {
        $departmentName = $user->department->name ?? 'N/A';
        $systemAdmins = User::where('role', 1)->get();
        
        foreach ($systemAdmins as $admin) {
            $admin->notify(new SystemNotification(
                'Yêu cầu đào tạo mới',
                "Phòng ban <strong>{$departmentName}</strong> vừa gửi yêu cầu đào tạo: <strong>{$trainingRequest->course_name}</strong>.",
                route('system.requests.show', $trainingRequest->id)
            ));
        }
    }
}