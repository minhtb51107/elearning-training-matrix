<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeCourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'image' => 'https://placehold.co/600x300/0ea5e9/white?text=' . urlencode(mb_strtoupper(mb_substr($this->name, 0, 3, 'UTF-8'))),
            'title' => $this->name,
            'description' => $this->description ?: 'Chưa có mô tả',
            'date' => $this->created_at->format('m/Y'),
            'type' => $this->target_audience === 'Toàn phòng' ? 'Bắt buộc' : 'Tự chọn'
        ];
    }
}