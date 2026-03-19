<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
<<<<<<< Updated upstream
    //
}
=======
    // Bật tính năng Xóa mềm (Không xóa thật khỏi DB, chỉ đánh dấu ngày xóa)
    use SoftDeletes;

    /**
     * Danh sách các cột ĐƯỢC PHÉP nhận dữ liệu trực tiếp từ mảng (Mass Assignment)
     */
    protected $fillable = [
        'code',
        'name',
        'description',
        'content',
        'target_audience',
        'format',
        'duration_minutes',
        'is_mandatory',
        'created_by',
    ];

    // ==========================================
    // KHAI BÁO MỐI QUAN HỆ (Relationships)
    // ==========================================
    
    /**
     * Khóa học này do ai tạo?
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function classes()
    {
        return $this->hasMany(CourseClass::class);
    }
}
>>>>>>> Stashed changes
