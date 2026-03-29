<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model {
    protected $fillable = ['quiz_id', 'class_enrollment_id', 'score', 'status', 'started_at', 'completed_at'];
    protected $casts = ['started_at' => 'datetime', 'completed_at' => 'datetime'];

    public function quiz() { return $this->belongsTo(Quiz::class); }
    public function enrollment() { return $this->belongsTo(ClassEnrollment::class, 'class_enrollment_id'); }
    public function answers() { return $this->hasMany(QuizAttemptAnswer::class); }
}