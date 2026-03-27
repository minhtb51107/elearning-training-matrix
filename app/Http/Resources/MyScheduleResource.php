<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class MyScheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $courseClass = $this->courseClass;
        $course = $courseClass ? $courseClass->course : null;
        $instructor = $courseClass ? $courseClass->instructor : null;
        
        $carbonDate = Carbon::parse($this->date);
        $dayOfWeekMap = [0 => 'CN', 1 => 'T2', 2 => 'T3', 3 => 'T4', 4 => 'T5', 5 => 'T6', 6 => 'T7'];
        $dayStr = $dayOfWeekMap[$carbonDate->dayOfWeek];
        
        return [
            'id' => $this->id,
            'title' => $this->title,
            'date' => $dayStr . ' ' . $carbonDate->format('d/m/y'),
            'class' => $courseClass ? $courseClass->name : '--',
            'course' => $course ? $course->name : '--',
            'instructor' => $instructor ? $instructor->name : '--',
            'format' => $courseClass ? $courseClass->format : '--', 
            'time' => Carbon::parse($this->start_time)->format('H:i') . '-' . Carbon::parse($this->end_time)->format('H:i'),
            'status' => $courseClass ? $courseClass->status : '--',
            'link' => $this->meet_link, 
            'room' => $this->room, 
        ];
    }
}