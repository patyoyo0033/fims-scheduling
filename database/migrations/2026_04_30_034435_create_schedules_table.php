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
    Schema::create('schedules', function (Blueprint $table) {
        $table->id();
        $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
        $table->foreignId('user_id')->constrained('users')->comment('อาจารย์ผู้สอน');
        $table->foreignId('room_id')->constrained('rooms');
        
        $table->string('student_group'); // เช่น กลุ่ม 1, กลุ่ม 2.1
        $table->integer('student_count'); // จำนวนนศ.ที่มาเรียน (เทียบกับ capacity ห้อง)
        
        // ฟิลด์สำคัญสำหรับการคำนวณ Conflict
        $table->date('teaching_date')->index(); // ทำ Index ให้ค้นหาตอนตรวจชนได้เร็วขึ้น
        $table->time('start_time');
        $table->time('end_time');
        
        // Workflow Status: draft, pending_approval, approved, revised
        $table->enum('status', ['draft', 'pending_approval', 'approved', 'revised'])->default('draft');
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
