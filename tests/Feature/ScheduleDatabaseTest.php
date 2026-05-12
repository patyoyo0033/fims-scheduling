<?php

namespace Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ScheduleDatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_has_expected_scheduling_schema_updates(): void
    {
        foreach (['planned_activity_count', 'planned_lecture_hours', 'planned_lab_hours'] as $column) {
            $this->assertTrue(Schema::hasColumn('course_offerings', $column));
            $this->assertTrue($this->columnIsNullable('course_offerings', $column));
            $this->assertStringContainsString('int', strtolower(Schema::getColumnType('course_offerings', $column, true)));
        }

        $this->assertTrue(Schema::hasColumn('student_groups', 'parent_id'));
        $this->assertTrue($this->columnIsNullable('student_groups', 'parent_id'));
        $this->assertForeignKeyExists('student_groups', 'parent_id', 'student_groups', 'id');

        foreach (['draft', 'pending_approval', 'approved', 'revised', 'cancelled'] as $status) {
            $this->assertStringContainsString($status, $this->statusColumnDefinition());
        }

        $this->assertTrue(Schema::hasTable('room_activity_types'));
        $this->assertCompositePrimaryKey('room_activity_types', ['room_id', 'activity_type_id']);
        $this->assertFalse(Schema::hasColumn('room_activity_types', 'created_at'));
        $this->assertFalse(Schema::hasColumn('room_activity_types', 'updated_at'));
    }

    public function test_it_sets_parent_id_null_when_parent_group_is_deleted(): void
    {
        $academicYearId = $this->createAcademicYear();
        $parentId = $this->createStudentGroup($academicYearId, 'Parent A');
        $childId = $this->createStudentGroup($academicYearId, 'Child A', $parentId);

        DB::table('student_groups')->where('id', $parentId)->delete();

        $this->assertDatabaseHas('student_groups', [
            'id' => $childId,
            'parent_id' => null,
        ]);
    }

    public function test_it_saves_cancelled_schedule_status(): void
    {
        $fixture = $this->createScheduleFixture();

        DB::table('schedules')->insert([
            'course_offering_id' => $fixture['course_offering_id'],
            'activity_type_id' => $fixture['activity_type_id'],
            'room_id' => $fixture['room_id'],
            'teaching_date' => '2026-05-18',
            'start_time' => '09:00',
            'end_time' => '11:00',
            'status' => 'cancelled',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertDatabaseHas('schedules', [
            'course_offering_id' => $fixture['course_offering_id'],
            'status' => 'cancelled',
        ]);
    }

    public function test_it_rejects_duplicate_room_activity_type_pairs(): void
    {
        $roomId = $this->createRoom();
        $activityTypeId = $this->createActivityType();

        DB::table('room_activity_types')->insert([
            'room_id' => $roomId,
            'activity_type_id' => $activityTypeId,
        ]);

        $this->expectException(QueryException::class);

        DB::table('room_activity_types')->insert([
            'room_id' => $roomId,
            'activity_type_id' => $activityTypeId,
        ]);
    }

    public function test_it_cascade_deletes_room_activity_type_rows_when_room_is_deleted(): void
    {
        $roomId = $this->createRoom();
        $activityTypeId = $this->createActivityType();

        DB::table('room_activity_types')->insert([
            'room_id' => $roomId,
            'activity_type_id' => $activityTypeId,
        ]);

        DB::table('rooms')->where('id', $roomId)->delete();

        $this->assertDatabaseMissing('room_activity_types', [
            'room_id' => $roomId,
            'activity_type_id' => $activityTypeId,
        ]);
    }

    private function createScheduleFixture(): array
    {
        $userId = $this->createUser('fixture@example.test');
        $academicYearId = $this->createAcademicYear();
        $courseId = $this->createCourse($userId);
        $courseOfferingId = $this->createCourseOffering($courseId, $academicYearId, $userId);

        return [
            'user_id' => $userId,
            'academic_year_id' => $academicYearId,
            'course_id' => $courseId,
            'course_offering_id' => $courseOfferingId,
            'room_id' => $this->createRoom(),
            'activity_type_id' => $this->createActivityType(),
            'student_group_id' => $this->createStudentGroup($academicYearId, 'Fixture Group'),
        ];
    }

    private function createUser(string $email): int
    {
        return DB::table('users')->insertGetId([
            'name' => 'Test User',
            'email' => $email,
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createAcademicYear(): int
    {
        return DB::table('academic_years')->insertGetId([
            'name' => '2569',
            'semester' => 1,
            'start_date' => '2026-06-01',
            'end_date' => '2026-10-31',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createCourse(int $coordinatorId): int
    {
        return DB::table('courses')->insertGetId([
            'course_code' => 'NSG101',
            'course_name' => 'Nursing Fundamentals',
            'credit' => 3,
            'coordinator_id' => $coordinatorId,
            'lecture_hours' => 30,
            'lab_hours' => 15,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createCourseOffering(int $courseId, int $academicYearId, int $coordinatorId): int
    {
        return DB::table('course_offerings')->insertGetId([
            'course_id' => $courseId,
            'academic_year_id' => $academicYearId,
            'coordinator_id' => $coordinatorId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createStudentGroup(int $academicYearId, string $groupName, ?int $parentId = null): int
    {
        return DB::table('student_groups')->insertGetId([
            'parent_id' => $parentId,
            'group_name' => $groupName,
            'year_level' => 1,
            'student_count' => 24,
            'academic_year_id' => $academicYearId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createRoom(): int
    {
        return DB::table('rooms')->insertGetId([
            'room_code' => 'RN101',
            'room_name' => 'Room 101',
            'capacity' => 40,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createActivityType(): int
    {
        return DB::table('activity_types')->insertGetId([
            'name' => 'Lecture',
            'is_practicum' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function columnIsNullable(string $table, string $column): bool
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            $database = DB::getDatabaseName();
            $result = DB::selectOne(
                'SELECT IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?',
                [$database, $table, $column]
            );

            return $result?->IS_NULLABLE === 'YES';
        }

        $columnInfo = collect(Schema::getColumns($table))->firstWhere('name', $column);

        return (bool) ($columnInfo['nullable'] ?? false);
    }

    private function statusColumnDefinition(): string
    {
        if (DB::getDriverName() === 'mysql') {
            $result = DB::selectOne('SHOW COLUMNS FROM schedules LIKE "status"');

            return (string) $result?->Type;
        }

        $definition = DB::selectOne(
            "SELECT sql FROM sqlite_master WHERE type = 'table' AND name = 'schedules'"
        );

        return (string) $definition?->sql;
    }

    private function assertForeignKeyExists(string $table, string $column, string $foreignTable, string $foreignColumn): void
    {
        if (DB::getDriverName() === 'mysql') {
            $database = DB::getDatabaseName();
            $result = DB::selectOne(
                'SELECT COUNT(*) AS aggregate FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME = ? AND REFERENCED_COLUMN_NAME = ?',
                [$database, $table, $column, $foreignTable, $foreignColumn]
            );

            $this->assertSame(1, (int) $result->aggregate);

            return;
        }

        $foreignKeys = DB::select("PRAGMA foreign_key_list('{$table}')");
        $match = collect($foreignKeys)->contains(fn ($key) => $key->from === $column
            && $key->table === $foreignTable
            && $key->to === $foreignColumn);

        $this->assertTrue($match);
    }

    private function assertCompositePrimaryKey(string $table, array $columns): void
    {
        if (DB::getDriverName() === 'mysql') {
            $database = DB::getDatabaseName();
            $primaryColumns = collect(DB::select(
                'SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = "PRIMARY" ORDER BY ORDINAL_POSITION',
                [$database, $table]
            ))->pluck('COLUMN_NAME')->all();
        } else {
            $primaryColumns = collect(DB::select("PRAGMA table_info('{$table}')"))
                ->filter(fn ($column) => $column->pk > 0)
                ->sortBy('pk')
                ->pluck('name')
                ->all();
        }

        $this->assertSame($columns, $primaryColumns);
    }
}
