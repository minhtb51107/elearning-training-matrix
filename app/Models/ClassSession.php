<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassSession extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function courseClass()
    {
        return $this->belongsTo(CourseClass::class);
    }
}