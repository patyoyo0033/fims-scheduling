<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropUnique('courses_course_code_unique'); // Drop old unique constraint
            $table->foreignId('curriculum_id')->nullable()->after('id')->constrained('curriculums');
            $table->string('name_th')->nullable()->after('course_name');
            $table->string('name_en')->nullable()->after('name_th');
            $table->integer('lecture_hours')->default(0)->after('credit');
            $table->integer('lab_hours')->default(0)->after('lecture_hours');
            $table->string('color_code')->nullable()->after('lab_hours');
            $table->softDeletes();
            
            // Allow duplicate course codes ONLY IF they are in different curriculums
            $table->unique(['curriculum_id', 'course_code'], 'courses_curriculum_code_unique');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropUnique('courses_curriculum_code_unique');
            $table->dropForeign(['curriculum_id']);
            $table->dropColumn(['curriculum_id', 'name_th', 'name_en', 'lecture_hours', 'lab_hours', 'color_code', 'deleted_at']);
            $table->unique('course_code', 'courses_course_code_unique');
        });
    }
};
