<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const PARENT_ID_COMMENT = 'Scheduling DB update: hierarchy parent group';

    public function up(): void
    {
        if (! Schema::hasColumn('student_groups', 'parent_id')) {
            Schema::table('student_groups', function (Blueprint $table) {
                $table->foreignId('parent_id')->nullable()->constrained('student_groups')->nullOnDelete();
            });
        }

        Schema::table('course_offerings', function (Blueprint $table) {
            if (! Schema::hasColumn('course_offerings', 'planned_activity_count')) {
                $table->integer('planned_activity_count')->nullable()->after('rejection_reason');
            }

            if (! Schema::hasColumn('course_offerings', 'planned_lecture_hours')) {
                $table->integer('planned_lecture_hours')->nullable()->after('planned_activity_count');
            }

            if (! Schema::hasColumn('course_offerings', 'planned_lab_hours')) {
                $table->integer('planned_lab_hours')->nullable()->after('planned_lecture_hours');
            }
        });

        // Recurring schedules are handled at application level by generating individual rows — no recurrence_rule field required.
        DB::statement(
            "ALTER TABLE schedules MODIFY status ENUM('draft','pending_approval','approved','revised','cancelled') NOT NULL DEFAULT 'draft'"
        );

        if (! Schema::hasTable('room_activity_types')) {
            Schema::create('room_activity_types', function (Blueprint $table) {
                $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
                $table->foreignId('activity_type_id')->constrained('activity_types')->cascadeOnDelete();

                $table->primary(['room_id', 'activity_type_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('room_activity_types');

        DB::table('schedules')
            ->where('status', 'cancelled')
            ->update(['status' => 'draft']);

        DB::statement(
            "ALTER TABLE schedules MODIFY status ENUM('draft','pending_approval','approved','revised') NOT NULL DEFAULT 'draft'"
        );

        Schema::table('course_offerings', function (Blueprint $table) {
            $columns = array_values(array_filter([
                Schema::hasColumn('course_offerings', 'planned_activity_count') ? 'planned_activity_count' : null,
                Schema::hasColumn('course_offerings', 'planned_lecture_hours') ? 'planned_lecture_hours' : null,
                Schema::hasColumn('course_offerings', 'planned_lab_hours') ? 'planned_lab_hours' : null,
            ]));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });

        if (
            Schema::hasColumn('student_groups', 'parent_id')
            && $this->columnComment('student_groups', 'parent_id') === self::PARENT_ID_COMMENT
        ) {
            Schema::table('student_groups', function (Blueprint $table) {
                $table->dropConstrainedForeignId('parent_id');
            });
        }
    }

    private function columnComment(string $table, string $column): ?string
    {
        $result = DB::selectOne(
            'SELECT COLUMN_COMMENT FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [DB::getDatabaseName(), $table, $column]
        );

        return $result?->COLUMN_COMMENT;
    }
};
