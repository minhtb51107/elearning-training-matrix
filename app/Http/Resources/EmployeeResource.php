<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\ClassEnrollment;
use App\Enums\EnrollmentStatusEnum;

class EmployeeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        // $this đại diện cho 1 Model User đang được duyệt qua
        $enrollments = ClassEnrollment::with('courseClass.course')
            ->where('user_id', $this->id)
            ->get();

        $learning = $enrollments->whereIn('status', [
            EnrollmentStatusEnum::ENROLLED->value, 
            EnrollmentStatusEnum::IN_PROGRESS->value
        ]);
        $completed = $enrollments->where('status', EnrollmentStatusEnum::COMPLETED->value);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'code' => $this->code ?? 'NV-' . str_pad($this->id, 3, '0', STR_PAD_LEFT),
            'department' => $this->department, // Lấy luôn relationship
            'activeClasses' => $learning->map(fn($en) => [
                'id' => $en->courseClass->id ?? 0,
                'course_name' => $en->courseClass->course->name ?? '--',
                'class_name' => $en->courseClass->name ?? '--',
                'progress' => $en->progress_percent ?? 0
            ])->values(),
            'history' => $completed->map(fn($en) => [
                'id' => $en->courseClass->id ?? 0,
                'course_name' => $en->courseClass->course->name ?? '--',
                'class_name' => $en->courseClass->name ?? '--',
                'date' => $en->updated_at->format('d/m/Y')
            ])->values(),
            'overview' => [
                'learning' => $learning->count(),
                'completed' => $completed->count(),
            ],
        ];
    }
}