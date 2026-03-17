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
    Schema::create('course_classes', function (Blueprint $table) {
        $table->id();
        $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
        $table->string('code')->unique();
        $table->string('name'); 
        
        $table->foreignId('instructor_id')->nullable()->constrained('users')->nullOnDelete();
        $table->string('instructor_name')->nullable()->comment('Dùng khi thuê giảng viên ngoài');
        
        $table->integer('capacity'); 
        $table->date('start_date');
        $table->date('end_date');
        $table->enum('status', ['draft', 'open', 'in_progress', 'completed'])->default('draft');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_classes');
    }
};
