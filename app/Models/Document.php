<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'course_id', 
        'course_class_id', 
        'name', 
        'type', 
        'url'
    ];

    public function course() {
        return $this->belongsTo(Course::class);
    }

    public function courseClass() {
        return $this->belongsTo(CourseClass::class, 'course_class_id');
    }
}