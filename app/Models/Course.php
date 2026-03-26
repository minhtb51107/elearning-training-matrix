<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'target_audience',
        'format',
        'duration',
        'content',
        'description',
        'reason',
        'status'
    ];

    // Khóa học có thể chứa nhiều yêu cầu đào tạo
    public function trainingRequests()
    {
        return $this->hasMany(TrainingRequest::class);
    }

    public function departments() {
        return $this->belongsToMany(Department::class, 'course_departments');
    }
    
    // Thêm hàm này để khai báo 1 Khóa học thì có nhiều Lớp học (1-N)
    public function courseClasses()
    {
        return $this->hasMany(CourseClass::class);
    }

    // Sửa lại hàm documents() trong file App\Models\Course.php
    public function documents()
    {
        return $this->hasMany(CourseDocument::class); 
    }

    public function lessons()
    {
        return $this->hasMany(CourseLesson::class)->orderBy('order_num');
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }
}