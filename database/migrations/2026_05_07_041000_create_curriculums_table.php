<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('curriculums', function (Blueprint $table) {
            $table->id();
            $table->string('curriculum_name_th');
            $table->string('curriculum_name_en')->nullable();
            $table->string('degree_level')->comment('เช่น B.N.S, M.N.S, Ph.D');
            $table->year('year_of_implementation')->comment('ปีที่เริ่มใช้หลักสูตร');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('curriculums');
    }
};
