<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('training_requests', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); 
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            
            // Các trường bắt buộc theo BA Spec (Bỏ nullable)
            $table->string('course_name'); 
            $table->string('target_audience'); 
            $table->text('content'); 
            // Đổi sang integer (đơn vị: Giờ) để dễ dàng validate > 0
            $table->integer('expected_duration'); 
            
            $table->text('notes')->nullable();
            
            // Trạng thái yêu cầu
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected', 'processed'])->default('draft');
            
            // Phản hồi từ HR khi từ chối
            $table->text('hr_feedback')->nullable(); 
            
            // Foreign Key liên kết tới courses (set null nếu KH bị xóa)
            $table->foreignId('course_id')->nullable()->constrained('courses')->nullOnDelete(); 
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void {
        Schema::dropIfExists('training_requests');
    }
};