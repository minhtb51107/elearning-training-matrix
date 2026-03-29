<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Department;
use App\Models\Instructor;
use App\Enums\RoleEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Faker\Factory as Faker;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('vi_VN');
        $defaultPassword = Hash::make('password'); // Mật khẩu chung là: password

        // 1. TẠO 5 PHÒNG BAN CHÍNH
        $departments = [
            'HR' => Department::create(['code' => 'HR', 'name' => 'Phòng Nhân sự & Đào tạo']),
            'IT' => Department::create(['code' => 'IT', 'name' => 'Khối Công nghệ thông tin']),
            'SALES' => Department::create(['code' => 'SALES', 'name' => 'Phòng Kinh doanh']),
            'MKT' => Department::create(['code' => 'MKT', 'name' => 'Phòng Marketing']),
            'ACC' => Department::create(['code' => 'ACC', 'name' => 'Phòng Kế toán - Tài chính']),
        ];

        // 2. TẠO ADMIN HỆ THỐNG (Role 1)
        $admin = User::create([
            'name' => 'Admin Đào Tạo',
            'email' => 'admin@elearning.com',
            'password' => $defaultPassword,
            'role' => RoleEnum::SYSTEM_ADMIN->value,
            'department_id' => $departments['HR']->id,
            'job_title' => 'Chuyên viên L&D',
            'is_manager' => false,
            'join_date' => Carbon::now()->subYears(2), // Làm 2 năm
        ]);

        // 3. TẠO TRƯỞNG PHÒNG CHO TỪNG PHÒNG BAN (Role 2)
        $deptAdmins = [];
        foreach ($departments as $code => $dept) {
            $email = $code === 'SALES' ? 'manager@sales.com' : 'manager_' . strtolower($code) . '@elearning.com';
            
            $deptAdmins[$code] = User::create([
                'name' => 'Trưởng Phòng ' . $code,
                'email' => $email,
                'password' => $defaultPassword,
                'role' => RoleEnum::DEPARTMENT_ADMIN->value,
                'department_id' => $dept->id,
                'job_title' => 'Giám đốc / Trưởng phòng',
                'is_manager' => true,
                'join_date' => Carbon::now()->subYears(4), // Làm 4 năm
            ]);
        }

        // --- BẮT ĐẦU TẠO NHÂN VIÊN (ROLE 3) DÙNG ĐỂ TEST CÁC BỘ LỌC ĐÀO TẠO ---
        
        $managerTitles = ['Trưởng nhóm', 'Phó phòng', 'Team Lead', 'Quản lý dự án'];
        $staffTitles = ['Chuyên viên', 'Nhân viên', 'Thực tập sinh', 'Cộng tác viên'];

        foreach ($departments as $code => $dept) {
            
            // 4A. TẠO CẤP QUẢN LÝ (ROLE 3 - Nhưng có is_manager = true)
            // Dùng để test đối tượng "Cấp quản lý"
            for ($i = 1; $i <= 2; $i++) {
                User::create([
                    'name' => $faker->name,
                    'email' => "lead_{$code}_{$i}@elearning.com",
                    'password' => $defaultPassword,
                    'role' => RoleEnum::EMPLOYEE->value,
                    'department_id' => $dept->id,
                    'job_title' => $faker->randomElement($managerTitles),
                    'is_manager' => true,
                    'join_date' => Carbon::now()->subMonths(rand(12, 36)),
                ]);
            }

            // 4B. TẠO NHÂN VIÊN MỚI ONBOARDING (ROLE 3 - join_date < 2 tháng)
            // Dùng để test đối tượng "Nhân viên mới"
            for ($i = 1; $i <= 3; $i++) {
                User::create([
                    'name' => $faker->name,
                    'email' => "newbie_{$code}_{$i}@elearning.com",
                    'password' => $defaultPassword,
                    'role' => RoleEnum::EMPLOYEE->value,
                    'department_id' => $dept->id,
                    'job_title' => $faker->randomElement($staffTitles),
                    'is_manager' => false,
                    'join_date' => Carbon::now()->subDays(rand(5, 45)), // Vào làm 5 đến 45 ngày trước
                ]);
            }

            // 4C. TẠO NHÂN VIÊN CŨ ĐÃ LÀM LÂU (ROLE 3 - join_date > 6 tháng)
            // Dùng để test đối tượng "Toàn công ty / Toàn phòng"
            for ($i = 1; $i <= 6; $i++) {
                $email = ($code === 'SALES' && $i === 1) ? 'employee@sales.com' : "staff_{$code}_{$i}@elearning.com";
                
                // Đảm bảo không bị trùng email employee@sales.com nếu chạy lại
                if (User::where('email', $email)->exists()) continue;

                User::create([
                    'name' => ($email === 'employee@sales.com') ? 'Nhân Viên Sales (Test)' : $faker->name,
                    'email' => $email,
                    'password' => $defaultPassword,
                    'role' => RoleEnum::EMPLOYEE->value,
                    'department_id' => $dept->id,
                    'job_title' => $faker->randomElement($staffTitles),
                    'is_manager' => false,
                    'join_date' => Carbon::now()->subMonths(rand(6, 48)),
                ]);
            }
        }

        // 5. TẠO GIẢNG VIÊN MẪU (NỘI BỘ VÀ BÊN NGOÀI)
        Instructor::create([
            'user_id' => $admin->id,
            'name' => $admin->name . ' (GV Nội bộ)',
            'type' => 'internal',
            'email' => $admin->email,
        ]);
        
        Instructor::create([
            'name' => 'Nguyễn Văn Chuyên Gia (Thuê ngoài)',
            'type' => 'external',
            'email' => 'expert@external.com',
            'phone' => '0987654321'
        ]);
        
        $this->command->info('Đã tạo thành công bộ dữ liệu lớn: Phòng ban, Admin, Trưởng phòng, Quản lý cấp trung, Lính mới, Lính cũ và Giảng viên!');
    }
}