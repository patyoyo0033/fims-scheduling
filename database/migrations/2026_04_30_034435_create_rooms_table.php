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
    Schema::create('rooms', function (Blueprint $table) {
        $table->id();
        $table->string('room_code')->unique(); // เช่น N401
        $table->string('room_name');
        $table->integer('capacity'); // ความจุนักศึกษา (ใช้ดัก Smart Warning)
        $table->string('location_type')->default('internal'); // internal (ห้องเรียนคณะ), external (แหล่งฝึก/Ward)
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
