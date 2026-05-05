<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * ตาราง course_offerings — รายวิชาที่เปิดสอนในแต่ละภาคเรียน
     */
    public function up(): void
    {
        Schema::create('course_offerings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')
                  ->constrained('courses')
                  ->cascadeOnDelete();                     // ลบรายวิชา → ลบ offering ด้วย
            $table->foreignId('academic_year_id')
                  ->constrained('academic_years')
                  ->cascadeOnDelete();                     // ลบปีการศึกษา → ลบ offering ด้วย
            $table->foreignId('coordinator_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();                        // ลบผู้ใช้ → set null (ไม่ลบ offering)
            $table->boolean('is_practicum')->default(false); // เป็นวิชาฝึกปฏิบัติ/ขึ้นวอร์ดหรือไม่
            $table->json('settings')->nullable();          // เงื่อนไขเพิ่มเติม เช่น อัตราส่วนอาจารย์:นักศึกษา
            $table->timestamps();

            // ป้องกันเปิดวิชาเดียวกันซ้ำในเทอมเดียวกัน
            $table->unique(['course_id', 'academic_year_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_offerings');
    }
};
