<?php

namespace App\Enums;

enum RoleEnum: int
{
    case SYSTEM_ADMIN = 1;
    case DEPARTMENT_ADMIN = 2;
    case EMPLOYEE = 3;

    // Tùy chọn: Hàm phụ trợ để lấy tên hiển thị
    public function label(): string
    {
        return match($this) {
            self::SYSTEM_ADMIN => 'Quản trị hệ thống',
            self::DEPARTMENT_ADMIN => 'Quản trị phòng ban',
            self::EMPLOYEE => 'Học viên',
        };
    }
}