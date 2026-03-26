<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseDocument extends Model
{
    protected $fillable = ['course_id', 'title', 'type', 'file_path', 'url'];
}