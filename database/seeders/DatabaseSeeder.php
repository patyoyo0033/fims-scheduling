<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\CourseOffering;
use App\Models\Room;
use App\Models\StudentGroup;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Users
        $admin = User::firstOrCreate(
            ['email' => 'admin@fims.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
            ]
        );

        $teacher1 = User::firstOrCreate(
            ['email' => 'teacher1@fims.com'],
            [
                'name' => 'Dr. Somsri (Teacher 1)',
                'password' => Hash::make('password'),
            ]
        );

        $teacher2 = User::firstOrCreate(
            ['email' => 'teacher2@fims.com'],
            [
                'name' => 'Ajarn Wipa (Teacher 2)',
                'password' => Hash::make('password'),
            ]
        );

        // 2. Academic Year
        $academicYear = AcademicYear::firstOrCreate(
            ['name' => '2569', 'semester' => 1],
            [
                'is_active' => true,
                'start_date' => '2026-08-01',
                'end_date' => '2026-12-31',
            ]
        );

        // 3. Rooms
        Room::firstOrCreate(
            ['room_code' => 'Lec-101'],
            [
                'room_name' => 'Lecture Room 1',
                'capacity' => 100,
                'location_type' => 'Lecture',
                'is_active' => true,
            ]
        );

        Room::firstOrCreate(
            ['room_code' => 'Lab-101'],
            [
                'room_name' => 'Nursing Lab 1',
                'capacity' => 30,
                'location_type' => 'Lab',
                'is_active' => true,
            ]
        );

        Room::firstOrCreate(
            ['room_code' => 'Ward-A'],
            [
                'room_name' => 'Pediatric Ward A',
                'capacity' => 15,
                'location_type' => 'Ward',
                'is_active' => true,
            ]
        );

        // 4. Courses
        $course1 = Course::firstOrCreate(
            ['course_code' => 'NS101'],
            [
                'course_name' => 'กายวิภาคศาสตร์และสรีรวิทยา (Anatomy and Physiology)',
                'credit' => 3,
            ]
        );

        $course2 = Course::firstOrCreate(
            ['course_code' => 'NS205'],
            [
                'course_name' => 'การพยาบาลพื้นฐาน (Basic Nursing Practicum)',
                'credit' => 2,
            ]
        );

        // 5. Student Groups
        StudentGroup::firstOrCreate(
            ['group_name' => 'กลุ่ม 1', 'academic_year_id' => $academicYear->id],
            [
                'year_level' => 1,
                'student_count' => 80,
            ]
        );

        StudentGroup::firstOrCreate(
            ['group_name' => 'กลุ่ม 1.1', 'academic_year_id' => $academicYear->id],
            [
                'year_level' => 1,
                'student_count' => 25,
            ]
        );

        StudentGroup::firstOrCreate(
            ['group_name' => 'กลุ่ม 1.2', 'academic_year_id' => $academicYear->id],
            [
                'year_level' => 1,
                'student_count' => 25,
            ]
        );

        // 6. Course Offerings
        CourseOffering::firstOrCreate(
            ['course_id' => $course1->id, 'academic_year_id' => $academicYear->id],
            [
                'coordinator_id' => $teacher1->id,
                'is_practicum' => false,
                'settings' => null,
            ]
        );

        CourseOffering::firstOrCreate(
            ['course_id' => $course2->id, 'academic_year_id' => $academicYear->id],
            [
                'coordinator_id' => $teacher2->id,
                'is_practicum' => true,
                'settings' => [
                    'max_students_per_group' => 8,
                ],
            ]
        );
    }
}
