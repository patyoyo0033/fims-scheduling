<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('location_types', function (Blueprint $table) {
            $table->id();
            $table->string('type_name')->unique()->comment('เช่น Lecture, Lab, Ward, Online');
            $table->string('color_code')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('location_types');
    }
};
