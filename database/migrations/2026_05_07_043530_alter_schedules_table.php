<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->foreignId('course_offering_id')->nullable()->after('id')->constrained('course_offerings')->cascadeOnDelete();
            $table->foreignId('activity_type_id')->nullable()->after('course_offering_id')->constrained('activity_types')->nullOnDelete();
            $table->foreignId('practicum_series_id')->nullable()->after('activity_type_id')->constrained('practicum_series')->nullOnDelete();
            $table->string('topic')->nullable()->after('end_time');
            $table->text('remark')->nullable()->after('topic');
            $table->softDeletes();
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->dropForeign(['course_id']);
            $table->dropForeign(['user_id']);
            $table->dropColumn(['course_id', 'user_id', 'student_group', 'student_count']);
        });
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->comment('อาจารย์ผู้สอน');
            $table->string('student_group');
            $table->integer('student_count');
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->dropForeign(['course_offering_id']);
            $table->dropForeign(['activity_type_id']);
            $table->dropForeign(['practicum_series_id']);
            $table->dropColumn(['course_offering_id', 'activity_type_id', 'practicum_series_id', 'topic', 'remark', 'deleted_at']);
        });
    }
};
