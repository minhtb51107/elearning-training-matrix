<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    public function classes() {
    return $this->hasMany(CourseClass::class, 'course_id');
}

public function trainingRequests() {
    return $this->hasMany(TrainingRequest::class);
}

// Lấy các phòng ban được phép học khóa này (từ bảng trung gian course_departments)
public function departments() {
    return $this->belongsToMany(Department::class, 'course_departments');
}
}
