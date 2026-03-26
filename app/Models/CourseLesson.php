<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CourseLesson extends Model
{
    protected $fillable = ['course_id', 'title', 'media_type', 'media_url', 'duration', 'order_num'];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}