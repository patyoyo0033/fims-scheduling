<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_groups', function (Blueprint $table) {
            $table->foreignId('curriculum_id')->nullable()->after('academic_year_id')->constrained('curriculums')->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->after('id')->constrained('student_groups')->nullOnDelete();
            $table->string('color_code')->nullable()->after('student_count');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('student_groups', function (Blueprint $table) {
            $table->dropForeign(['curriculum_id']);
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['curriculum_id', 'parent_id', 'color_code', 'deleted_at']);
        });
    }
};
