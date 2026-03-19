<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrainingRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'department_id', 'requester_id', 'course_name', 
        'target_audience', 'content', 'expected_duration', 
        'notes', 'status', 'hr_feedback', 'course_id'
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }
}