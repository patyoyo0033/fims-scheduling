<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * ตาราง student_groups — เก็บข้อมูลกลุ่มนักศึกษาแต่ละชั้นปี
     */
    public function up(): void
    {
        Schema::create('student_groups', function (Blueprint $table) {
            $table->id();
            $table->string('group_name');                  // เช่น "กลุ่ม 1.1", "Group 2.3"
            $table->unsignedTinyInteger('year_level');     // ชั้นปี 1-4
            $table->unsignedInteger('student_count')->default(0); // จำนวนนักศึกษาในกลุ่ม
            $table->foreignId('academic_year_id')
                  ->constrained('academic_years')
                  ->cascadeOnDelete();                     // ลบปีการศึกษา → ลบกลุ่มนักศึกษาด้วย
            $table->timestamps();

            // ป้องกันชื่อกลุ่มซ้ำในปีการศึกษาเดียวกัน
            $table->unique(['group_name', 'academic_year_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_groups');
    }
};
