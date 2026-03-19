<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->tinyInteger('role')->default(3)->after('password');
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete()->after('role');
            $table->string('employee_code')->unique()->nullable()->after('id');
            
            // THÊM MỚI TỪ MOCKUP
            $table->string('position')->nullable()->after('department_id')->comment('Chức vụ: Nhân viên, Team Lead...');
            $table->string('training_status')->default('Chưa đạt yêu cầu')->after('position');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn(['role', 'department_id', 'employee_code', 'position', 'training_status']);
        });
    }
};