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
        // THÊM 3 TRƯỜNG MỚI CHO TÍNH NĂNG CHỈ ĐỊNH
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

    // THÊM RELATIONSHIP: Liên kết tới người Trưởng phòng đã ra lệnh chỉ định
    public function assignedBy() {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}