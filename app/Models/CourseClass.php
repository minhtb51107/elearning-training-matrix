<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseClass extends Model
{
    protected $fillable = [
        'course_id',
        'code',
        'name',
        'instructor_id',
        'start_date',
        'end_date',
        'max_students',
        'format',
        'department_id',
        'status'
    ];

    public function course() {
        return $this->belongsTo(Course::class);
    }

    public function instructor() {
        return $this->belongsTo(Instructor::class);
    }

    // 👇 THÊM HÀM NÀY VÀO 👇
    public function department() {
        return $this->belongsTo(Department::class);
    }

    public function enrollments() {
        return $this->hasMany(ClassEnrollment::class, 'class_id');
    }

    public function sessions() {
        return $this->hasMany(ClassSession::class, 'class_id');
    }
}