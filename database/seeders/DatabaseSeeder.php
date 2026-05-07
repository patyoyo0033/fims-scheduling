<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\CourseOffering;
use App\Models\Room;
use App\Models\StudentGroup;
use App\Models\User;
use App\Models\Curriculum;
use App\Models\LocationType;
use App\Models\ActivityType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

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

        // 2.5 Curriculums
        $curriculum = Curriculum::firstOrCreate(
            ['curriculum_name_th' => 'พยาบาลศาสตรบัณฑิต'],
            [
                'curriculum_name_en' => 'Bachelor of Nursing Science',
                'degree_level' => 'B.N.S',
                'year_of_implementation' => 2026,
            ]
        );

        // 2.6 Location Types
        $locLec = LocationType::firstOrCreate(['type_name' => 'Lecture']);
        $locLab = LocationType::firstOrCreate(['type_name' => 'Lab']);
        $locWard = LocationType::firstOrCreate(['type_name' => 'Ward']);

        // 3. Rooms
        Room::firstOrCreate(
            ['room_code' => 'Lec-101'],
            [
                'room_name' => 'Lecture Room 1',
                'capacity' => 100,
                'location_type_id' => $locLec->id,
                'is_active' => true,
            ]
        );

        Room::firstOrCreate(
            ['room_code' => 'Lab-101'],
            [
                'room_name' => 'Nursing Lab 1',
                'capacity' => 30,
                'location_type_id' => $locLab->id,
                'is_active' => true,
            ]
        );

        Room::firstOrCreate(
            ['room_code' => 'Ward-A'],
            [
                'room_name' => 'Pediatric Ward A',
                'capacity' => 15,
                'location_type_id' => $locWard->id,
                'is_active' => true,
            ]
        );

        // 4. Courses
        $course1 = Course::firstOrCreate(
            ['course_code' => 'NS101', 'curriculum_id' => $curriculum->id],
            [
                'course_name' => 'กายวิภาคศาสตร์และสรีรวิทยา (Anatomy and Physiology)',
                'name_th' => 'กายวิภาคศาสตร์และสรีรวิทยา',
                'name_en' => 'Anatomy and Physiology',
                'credit' => 3,
            ]
        );

        $course2 = Course::firstOrCreate(
            ['course_code' => 'NS205', 'curriculum_id' => $curriculum->id],
            [
                'course_name' => 'การพยาบาลพื้นฐาน (Basic Nursing Practicum)',
                'name_th' => 'การพยาบาลพื้นฐาน',
                'name_en' => 'Basic Nursing Practicum',
                'credit' => 2,
            ]
        );

        // 5. Student Groups
        $mainGroup = StudentGroup::firstOrCreate(
            ['group_name' => 'กลุ่ม 1', 'academic_year_id' => $academicYear->id],
            [
                'curriculum_id' => $curriculum->id,
                'year_level' => 1,
                'student_count' => 80,
            ]
        );

        StudentGroup::firstOrCreate(
            ['group_name' => 'กลุ่ม 1.1', 'academic_year_id' => $academicYear->id],
            [
                'parent_id' => $mainGroup->id,
                'curriculum_id' => $curriculum->id,
                'year_level' => 1,
                'student_count' => 25,
            ]
        );

        StudentGroup::firstOrCreate(
            ['group_name' => 'กลุ่ม 1.2', 'academic_year_id' => $academicYear->id],
            [
                'parent_id' => $mainGroup->id,
                'curriculum_id' => $curriculum->id,
                'year_level' => 1,
                'student_count' => 25,
            ]
        );

        // 5.5 Activity Types
        ActivityType::firstOrCreate(
            ['name' => 'Lecture'],
            ['is_practicum' => false, 'settings' => null]
        );
        ActivityType::firstOrCreate(
            ['name' => 'Lab'],
            ['is_practicum' => true, 'settings' => ['max_students_per_group' => 8]]
        );
        ActivityType::firstOrCreate(
            ['name' => 'Ward'],
            ['is_practicum' => true, 'settings' => ['max_students_per_group' => 6]]
        );

        // 6. Course Offerings
        $offering1 = CourseOffering::firstOrCreate(
            ['course_id' => $course1->id, 'academic_year_id' => $academicYear->id],
            [
                'coordinator_id' => $teacher1->id,
                'approval_status' => 'published',
            ]
        );

        $offering2 = CourseOffering::firstOrCreate(
            ['course_id' => $course2->id, 'academic_year_id' => $academicYear->id],
            [
                'coordinator_id' => $teacher2->id,
                'approval_status' => 'published',
            ]
        );
        
        // 7. Course Offering Instructors (M:N)
        DB::table('course_offering_instructors')->insertOrIgnore([
            ['course_offering_id' => $offering1->id, 'user_id' => $teacher1->id],
            ['course_offering_id' => $offering2->id, 'user_id' => $teacher2->id],
            ['course_offering_id' => $offering2->id, 'user_id' => $teacher1->id], // Teacher 1 also teaches course 2
        ]);
    }
}
