<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'department_id',
        'role',
        // Thêm 3 trường HR mới
        'job_title',
        'is_manager',
        'join_date'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => \App\Enums\RoleEnum::class,
            // Định dạng kiểu dữ liệu cho trường mới
            'is_manager' => 'boolean',
            'join_date' => 'date',
        ];
    }

    public function department() {
        return $this->belongsTo(Department::class);
    }

    // Lấy danh sách lớp nhân viên này đã đăng ký
    public function enrollments() {
        return $this->hasMany(ClassEnrollment::class);
    }
}