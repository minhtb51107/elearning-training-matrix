<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            
            // Các trường mới bổ sung theo form BA
            $table->string('target_audience')->nullable();
            $table->enum('format', ['Online', 'Offline', 'Hybrid'])->default('Offline');
            $table->integer('duration')->nullable(); // Tính bằng giờ
            $table->text('content')->nullable();
            $table->text('description')->nullable();
            $table->text('reason')->nullable(); // Lý do tạo (nếu tự đề xuất)
            
            // Trạng thái theo BA: Chưa có lớp, Đang triển khai, Đang mở, Đã kết thúc
            $table->string('status')->default('Chưa có lớp');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};