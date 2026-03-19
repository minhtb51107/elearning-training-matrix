<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->string('code')->unique();
            $table->string('name'); 
            
            // CẬP NHẬT: Trỏ về bảng instructors (sẽ tạo ở phần 2)
            $table->foreignId('instructor_id')->nullable()->constrained('instructors')->nullOnDelete();
            
            $table->integer('capacity'); 
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['draft', 'open', 'in_progress', 'completed'])->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_classes');
    }
};