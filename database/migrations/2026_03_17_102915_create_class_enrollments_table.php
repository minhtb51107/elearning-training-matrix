<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('class_enrollments', function (Blueprint $table) {
            $table->id();
            // Lớp xóa -> Xóa đăng ký
            $table->foreignId('course_class_id')->constrained('course_classes')->cascadeOnDelete();
            
            // CẤM XÓA User nếu họ đã có lịch sử học
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            
            $table->enum('status', ['enrolled', 'in_progress', 'completed', 'failed'])->default('enrolled');
            $table->integer('score')->nullable(); 
            
            // Thêm tracking tiến độ (chuẩn bị cho tương lai)
            $table->tinyInteger('progress_percent')->default(0)->comment('Từ 0 đến 100');
            
            $table->timestamp('enrolled_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            
            $table->unique(['course_class_id', 'user_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_enrollments');
    }
};
