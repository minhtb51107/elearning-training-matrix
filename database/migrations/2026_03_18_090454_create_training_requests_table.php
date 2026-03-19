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
            $table->string('course_name'); 
            $table->string('target_audience')->nullable(); 
            $table->text('content'); 
            $table->string('expected_duration')->nullable(); 
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected', 'processed'])->default('draft');
            $table->text('hr_feedback')->nullable(); 
            $table->unsignedBigInteger('course_id')->nullable(); // Sẽ được điền khi Admin tạo KH
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void {
        Schema::dropIfExists('training_requests');
    }
};