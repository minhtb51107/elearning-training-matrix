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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            
            $table->string('code', 50)->unique()->index(); 
            
            $table->string('name');
            $table->text('description')->nullable();
            $table->longText('content')->nullable();
            $table->string('target_audience')->nullable();
            
            $table->enum('format', ['online', 'offline', 'hybrid'])->default('online');
            
            $table->integer('duration_minutes')->comment('Thời lượng học tính bằng phút');
            
            $table->boolean('is_mandatory')->default(false);
            
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->softDeletes(); 
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
