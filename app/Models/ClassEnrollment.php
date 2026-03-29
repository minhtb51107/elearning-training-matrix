<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassEnrollment extends Model
{
    protected $fillable = [
        'course_class_id', 
        'user_id', 
        'status', 
        'final_score', 
        'certificate_url', 
        'progress_percent',
        'enrolled_at',
        'completed_at',
        'is_mandatory',
        'deadline',
        'assigned_by'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function courseClass() {
        return $this->belongsTo(CourseClass::class, 'course_class_id');
    }

    public function assignedBy() {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    // THÊM MỚI: Lịch sử làm bài trắc nghiệm của học viên này
    public function quizAttempts() {
        return $this->hasMany(QuizAttempt::class);
    }
}