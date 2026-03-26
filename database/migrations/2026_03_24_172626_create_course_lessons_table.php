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
        Schema::create('course_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('title'); // Tên bài giảng
            $table->string('media_type')->default('youtube'); // youtube, video_upload, pdf_slide
            $table->string('media_url')->nullable(); // Link youtube hoặc đường dẫn file
            $table->integer('duration')->default(0); // Thời lượng (phút)
            $table->integer('order_num')->default(0); // Số thứ tự bài học
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_lessons');
    }
};
