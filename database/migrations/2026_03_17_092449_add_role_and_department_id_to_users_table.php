<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Role: 1 = Admin Hệ thống, 2 = Admin Phòng ban, 3 = Nhân viên
            $table->tinyInteger('role')->default(3)->after('password');
            
            // Khóa ngoại nối sang bảng departments
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete()->after('role');
            
            // Mã nhân viên
            $table->string('employee_code')->unique()->nullable()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn(['role', 'department_id', 'employee_code']);
        });
    }
};
