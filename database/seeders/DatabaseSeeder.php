<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Department;
use App\Models\Instructor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. TẠO PHÒNG BAN MẪU
        $hrDept = Department::create(['code' => 'HR', 'name' => 'Phòng Đào tạo & Nhân sự']);
        $salesDept = Department::create(['code' => 'SALES', 'name' => 'Phòng Kinh doanh']);

        $defaultPassword = Hash::make('password'); // Mật khẩu chung là: password

        // 2. TẠO ADMIN HỆ THỐNG (Phòng Đào tạo) - Role 1
        $admin = User::create([
            'name' => 'Admin Đào Tạo',
            'email' => 'admin@elearning.com',
            'password' => $defaultPassword,
            'role' => 1,
            'department_id' => $hrDept->id,
            'employee_code' => 'EMP-001',
            'position' => 'Trưởng phòng Đào tạo',
        ]);

        // 3. TẠO ADMIN PHÒNG BAN (Phòng Kinh doanh) - Role 2
        User::create([
            'name' => 'Trưởng Phòng Sales',
            'email' => 'manager@sales.com',
            'password' => $defaultPassword,
            'role' => 2,
            'department_id' => $salesDept->id,
            'employee_code' => 'EMP-002',
            'position' => 'Trưởng phòng Kinh doanh',
        ]);

        // 4. TẠO NHÂN VIÊN (Phòng Kinh doanh) - Role 3
        User::create([
            'name' => 'Nhân Viên Sales',
            'email' => 'employee@sales.com',
            'password' => $defaultPassword,
            'role' => 3,
            'department_id' => $salesDept->id,
            'employee_code' => 'EMP-003',
            'position' => 'Nhân viên bán hàng',
        ]);

        // 5. TẠO GIẢNG VIÊN MẪU
        Instructor::create([
            'user_id' => $admin->id,
            'name' => 'Admin Đào Tạo (GV Nội bộ)',
            'type' => 'internal',
            'email' => 'admin@elearning.com',
        ]);
        
        $this->command->info('Đã tạo thành công 3 tài khoản (Admin, Manager, Employee) và các dữ liệu mẫu!');
    }
}