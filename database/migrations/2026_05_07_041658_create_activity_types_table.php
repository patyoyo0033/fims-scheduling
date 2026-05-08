<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('เช่น Lecture, Lab, Ward, SDL, ปฐมนิเทศ');
            $table->boolean('is_practicum')->default(false)->comment('ใช่คาบปฏิบัติหรือไม่');
            $table->json('settings')->nullable()->comment('เงื่อนไขเพิ่มเติม เช่น max_students');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_types');
    }
};
