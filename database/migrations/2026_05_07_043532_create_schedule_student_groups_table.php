<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedule_student_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('schedules')->cascadeOnDelete();
            $table->foreignId('student_group_id')->constrained('student_groups')->cascadeOnDelete();
            $table->timestamps();
            
            $table->unique(['schedule_id', 'student_group_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_student_groups');
    }
};
