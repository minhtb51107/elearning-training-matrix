<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrainingRequest extends Model
{
    use SoftDeletes;

    // Định nghĩa Constants cho Status (Rất quan trọng để check logic toàn dự án)
    public const STATUS_DRAFT = 'draft';         // Nháp
    public const STATUS_PENDING = 'pending';     // Chờ duyệt
    public const STATUS_APPROVED = 'approved';   // Đã duyệt
    public const STATUS_REJECTED = 'rejected';   // Từ chối
    public const STATUS_PROCESSED = 'processed'; // Đã xử lý (đã tạo khóa học)

    protected $fillable = [
        'code', 'department_id', 'requester_id', 'course_name', 
        'target_audience', 'content', 'expected_duration', 
        'notes', 'status', 'hr_feedback', 'course_id'
    ];

    // --- RELATIONSHIPS ---

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    // --- HELPER METHODS (Hỗ trợ check logic trong Controller/Policy) ---
    
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    // Theo BA rule: Chỉ cho phép chỉnh sửa khi đang Nháp
    public function canBeEdited(): bool
    {
        return $this->isDraft();
    }
}