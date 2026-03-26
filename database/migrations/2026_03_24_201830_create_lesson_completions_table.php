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
        Schema::create('lesson_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_lesson_id')->constrained('course_lessons')->cascadeOnDelete();
            $table->foreignId('course_class_id')->constrained('course_classes')->cascadeOnDelete();
            $table->timestamps();

            // Đảm bảo 1 user chỉ được đánh dấu hoàn thành 1 bài 1 lần trong 1 lớp
            $table->unique(['user_id', 'course_lesson_id', 'course_class_id'], 'unique_lesson_completion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_completions');
    }
};
