<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseClass extends Model
{
    public function course() {
    return $this->belongsTo(Course::class);
}

public function instructor() {
    return $this->belongsTo(Instructor::class);
}

public function enrollments() {
    return $this->hasMany(ClassEnrollment::class, 'class_id');
}

public function sessions() {
    return $this->hasMany(ClassSession::class, 'class_id');
}
}
