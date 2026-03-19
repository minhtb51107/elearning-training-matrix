<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_class_id')->constrained('course_classes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            
            $table->enum('status', ['enrolled', 'in_progress', 'completed', 'failed'])->default('enrolled');
            
            // CẬP NHẬT TỪ MOCKUP
            $table->decimal('final_score', 5, 2)->nullable()->comment('Điểm tổng kết cuối cùng');
            $table->string('certificate_url')->nullable()->comment('Link PDF chứng chỉ');
            $table->tinyInteger('progress_percent')->default(0)->comment('Từ 0 đến 100');
            
            $table->timestamp('enrolled_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            
            $table->unique(['course_class_id', 'user_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_enrollments');
    }
};