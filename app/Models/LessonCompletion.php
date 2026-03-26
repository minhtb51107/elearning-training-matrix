<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonCompletion extends Model
{
    protected $fillable = ['user_id', 'course_lesson_id', 'course_class_id'];
}