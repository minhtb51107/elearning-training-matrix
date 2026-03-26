<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Người nộp
            $table->foreignId('course_class_id')->constrained()->cascadeOnDelete(); // Nộp cho lớp nào
            
            $table->text('content')->nullable(); // Nội dung text học viên làm
            $table->string('file_url')->nullable(); // Link file đính kèm (nếu nộp file Word/Excel)
            
            $table->string('status')->default('pending'); // pending (Chờ chấm), graded (Đã chấm)
            $table->integer('score')->nullable(); // Điểm số (0-100)
            $table->text('feedback')->nullable(); // Lời phê của Giảng viên/Admin
            $table->foreignId('graded_by')->nullable()->constrained('users')->nullOnDelete(); // Ai là người chấm
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
