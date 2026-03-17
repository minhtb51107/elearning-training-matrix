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
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->string('name');
            
            // Đổi tên để tránh keyword SQL, default là 1
            $table->integer('sort_order')->default(1); 
            
            $table->string('video_url')->nullable();
            $table->text('document_url')->nullable();
            $table->timestamps();

            // Đảm bảo UI không bị loạn: Trong 1 khóa học, không có 2 bài trùng số thứ tự
            $table->unique(['course_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
