<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ScheduleControllerTest extends TestCase
{
    use RefreshDatabase;

    // BUG: ScheduleForm.vue sends user_id, but ScheduleController::store() validates instructor_ids; align the request contract.
    public function test_post_schedules_with_ui_user_id_payload_passes_validation(): void
    {
        $fixture = $this->createScheduleFixture();

        $response = $this->actingAs($fixture['user'])->post(route('schedules.store'), [
            'course_offering_id' => $fixture['course_offering_id'],
            'activity_type_id' => $fixture['activity_type_id'],
            'user_id' => $fixture['instructor_id'],
            'teaching_date' => '2026-05-18',
            'start_time' => '09:00',
            'end_time' => '11:00',
            'is_recurring' => false,
            'repeat_weeks' => 1,
            'is_rotation' => false,
            'room_id' => $fixture['room_id'],
            'student_group_id' => $fixture['student_group_id'],
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('schedules', [
            'course_offering_id' => $fixture['course_offering_id'],
            'status' => 'draft',
        ]);
    }

    // BUG: routes/web.php registers schedules.check-conflict, but ScheduleController::checkConflict() is missing.
    public function test_check_conflict_route_resolves_to_an_existing_controller_method(): void
    {
        $this->assertTrue(Route::has('schedules.check-conflict'));

        $fixture = $this->createScheduleFixture();

        $response = $this->actingAs($fixture['user'])->post(route('schedules.check-conflict'), [
            'teaching_date' => '2026-05-18',
            'start_time' => '09:00',
            'end_time' => '11:00',
            'room_id' => $fixture['room_id'],
            'student_group_id' => $fixture['student_group_id'],
            'instructor_ids' => [$fixture['instructor_id']],
        ]);

        $response->assertOk();
    }

    public function test_post_schedules_with_instructor_ids_creates_schedule(): void
    {
        $fixture = $this->createScheduleFixture();

        $response = $this->actingAs($fixture['user'])->post(route('schedules.store'), [
            'course_offering_id' => $fixture['course_offering_id'],
            'activity_type_id' => $fixture['activity_type_id'],
            'instructor_ids' => [$fixture['instructor_id']],
            'teaching_date' => '2026-05-18',
            'start_time' => '09:00',
            'end_time' => '11:00',
            'is_recurring' => false,
            'repeat_weeks' => 1,
            'is_rotation' => false,
            'room_id' => $fixture['room_id'],
            'student_group_id' => $fixture['student_group_id'],
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('schedules', [
            'course_offering_id' => $fixture['course_offering_id'],
            'activity_type_id' => $fixture['activity_type_id'],
            'room_id' => $fixture['room_id'],
            'teaching_date' => '2026-05-18',
        ]);
    }

    public function test_created_schedule_has_draft_status_by_default(): void
    {
        $fixture = $this->createScheduleFixture();

        $this->actingAs($fixture['user'])->post(route('schedules.store'), [
            'course_offering_id' => $fixture['course_offering_id'],
            'activity_type_id' => $fixture['activity_type_id'],
            'instructor_ids' => [$fixture['instructor_id']],
            'teaching_date' => '2026-05-18',
            'start_time' => '09:00',
            'end_time' => '11:00',
            'is_recurring' => false,
            'repeat_weeks' => 1,
            'is_rotation' => false,
            'room_id' => $fixture['room_id'],
            'student_group_id' => $fixture['student_group_id'],
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('schedules', [
            'course_offering_id' => $fixture['course_offering_id'],
            'status' => 'draft',
        ]);
    }

    private function createScheduleFixture(): array
    {
        $user = User::query()->create([
            'name' => 'Test User',
            'email' => 'user-' . uniqid() . '@example.test',
            'password' => bcrypt('password'),
        ]);

        $instructor = User::query()->create([
            'name' => 'Test Instructor',
            'email' => 'instructor-' . uniqid() . '@example.test',
            'password' => bcrypt('password'),
        ]);

        $academicYearId = DB::table('academic_years')->insertGetId([
            'name' => '2569',
            'semester' => 1,
            'start_date' => '2026-06-01',
            'end_date' => '2026-10-31',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $courseId = DB::table('courses')->insertGetId([
            'course_code' => 'NSG' . random_int(100, 999),
            'course_name' => 'Nursing Fundamentals',
            'credit' => 3,
            'coordinator_id' => $user->id,
            'lecture_hours' => 30,
            'lab_hours' => 15,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $courseOfferingId = DB::table('course_offerings')->insertGetId([
            'course_id' => $courseId,
            'academic_year_id' => $academicYearId,
            'coordinator_id' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $roomId = DB::table('rooms')->insertGetId([
            'room_code' => 'RN' . random_int(100, 999),
            'room_name' => 'Room 101',
            'capacity' => 40,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $activityTypeId = DB::table('activity_types')->insertGetId([
            'name' => 'Lecture ' . uniqid(),
            'is_practicum' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $studentGroupId = DB::table('student_groups')->insertGetId([
            'group_name' => 'Group ' . uniqid(),
            'year_level' => 1,
            'student_count' => 24,
            'academic_year_id' => $academicYearId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'user' => $user,
            'instructor_id' => $instructor->id,
            'academic_year_id' => $academicYearId,
            'course_id' => $courseId,
            'course_offering_id' => $courseOfferingId,
            'room_id' => $roomId,
            'activity_type_id' => $activityTypeId,
            'student_group_id' => $studentGroupId,
        ];
    }
}
