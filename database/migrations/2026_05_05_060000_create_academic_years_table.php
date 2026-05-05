<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * ตาราง academic_years — เก็บข้อมูลปีการศึกษาและภาคเรียน
     */
    public function up(): void
    {
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->string('name');                        // เช่น "2569"
            $table->unsignedTinyInteger('semester');        // 1, 2, 3 (ภาคฤดูร้อน)
            $table->date('start_date');                    // วันเริ่มต้นภาคเรียน
            $table->date('end_date');                      // วันสิ้นสุดภาคเรียน
            $table->boolean('is_active')->default(false);  // ภาคเรียนที่กำลังใช้งานอยู่
            $table->timestamps();

            // ป้องกันข้อมูลซ้ำ: ปีการศึกษา + เทอม ต้องไม่ซ้ำกัน
            $table->unique(['name', 'semester']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_years');
    }
};
