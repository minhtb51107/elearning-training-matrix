<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('class_enrollments', function (Blueprint $table) {
            $table->boolean('is_mandatory')->default(false)->after('status')->comment('Đánh dấu lớp bắt buộc');
            $table->dateTime('deadline')->nullable()->after('is_mandatory')->comment('Hạn chót hoàn thành');
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete()->after('deadline')->comment('ID Trưởng phòng chỉ định');
        });
    }

    public function down(): void
    {
        Schema::table('class_enrollments', function (Blueprint $table) {
            $table->dropForeign(['assigned_by']);
            $table->dropColumn(['is_mandatory', 'deadline', 'assigned_by']);
        });
    }
};
