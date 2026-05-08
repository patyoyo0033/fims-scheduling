<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instructor_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('employee_id')->nullable()->unique();
            $table->string('department')->nullable()->comment('ภาควิชา');
            $table->string('academic_position')->nullable()->comment('ตำแหน่งทางวิชาการ');
            $table->string('phone_number')->nullable();
            $table->boolean('is_visiting')->default(false)->comment('อาจารย์พิเศษ');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instructor_profiles');
    }
};
