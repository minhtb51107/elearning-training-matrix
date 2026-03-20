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
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('code')->unique();
            $table->string('name');
            $table->foreignId('instructor_id')->nullable()->constrained('instructors')->nullOnDelete();
            
            // Các cột bổ sung theo chuẩn BA Specification
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('max_students');
            $table->enum('format', ['Online', 'Offline', 'Hybrid'])->default('Offline');
            
            // Phạm vi áp dụng (null = Toàn công ty)
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            
            // Trạng thái: Nháp, Mở đăng ký, Đang học, Kết thúc
            $table->string('status')->default('Nháp');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_classes');
    }
};