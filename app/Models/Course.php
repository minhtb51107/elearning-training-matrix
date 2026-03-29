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
        'description', 
        'reason', 
        'status',
        'content'
    ];

    public function trainingRequests() {
        return $this->hasMany(TrainingRequest::class);
    }

    public function departments() {
        return $this->belongsToMany(Department::class, 'course_departments');
    }
    
    public function courseClasses() {
        return $this->hasMany(CourseClass::class);
    }

    public function documents() {
        return $this->hasMany(CourseDocument::class); 
    }

    public function lessons() {
        return $this->hasMany(CourseLesson::class)->orderBy('order_num');
    }

    public function assignments() {
        return $this->hasMany(Assignment::class);
    }

    // THÊM MỚI: Liên kết với các bài trắc nghiệm
    public function quizzes() {
        return $this->hasMany(Quiz::class);
    }
}