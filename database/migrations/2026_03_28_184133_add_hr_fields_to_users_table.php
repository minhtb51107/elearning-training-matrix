<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Thêm các cột phục vụ logic phân loại nhân sự
            $table->string('job_title')->nullable()->after('department_id')->comment('Chức danh công việc');
            $table->boolean('is_manager')->default(false)->after('job_title')->comment('Là quản lý cấp trung');
            $table->date('join_date')->nullable()->after('is_manager')->comment('Ngày gia nhập / Onboarding');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['job_title', 'is_manager', 'join_date']);
        });
    }
};