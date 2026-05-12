# Database Design Snapshot

This snapshot records the current scheduling database shape before pushing the project to GitHub.

## Runtime Database

- Target database: MySQL 8.0+
- Test database should also be MySQL, not SQLite.
- Reason: scheduling migrations use MySQL-specific syntax such as `ALTER TABLE ... MODIFY` for enum changes.

## Core Tables

### Master Data

- `users`
- `curriculums`
- `academic_years`
- `location_types`
- `rooms`
- `activity_types`
- `student_groups`
- `instructor_profiles`

### Course Management

- `courses`
- `course_offerings`
- `course_offering_instructors`
- `practicum_series`

### Scheduling

- `schedules`
- `schedule_instructors`
- `schedule_student_groups`
- `room_activity_types`

### Platform Tables

- `permissions`, `roles`, and Spatie permission pivot tables
- `cache`, `cache_locks`
- `jobs`, `job_batches`, `failed_jobs`
- `password_reset_tokens`, `sessions`

## Key Relationships

- `courses.curriculum_id` -> `curriculums.id`
- `courses.coordinator_id` -> `users.id`
- `course_offerings.course_id` -> `courses.id`
- `course_offerings.academic_year_id` -> `academic_years.id`
- `course_offerings.coordinator_id` -> `users.id` with `ON DELETE SET NULL`
- `student_groups.academic_year_id` -> `academic_years.id`
- `student_groups.curriculum_id` -> `curriculums.id` with `ON DELETE SET NULL`
- `student_groups.parent_id` -> `student_groups.id` with `ON DELETE SET NULL`
- `rooms.location_type_id` -> `location_types.id` with `ON DELETE SET NULL`
- `schedules.course_offering_id` -> `course_offerings.id`
- `schedules.activity_type_id` -> `activity_types.id` with `ON DELETE SET NULL`
- `schedules.practicum_series_id` -> `practicum_series.id` with `ON DELETE SET NULL`
- `schedules.room_id` -> `rooms.id`
- `schedule_instructors.schedule_id` -> `schedules.id`
- `schedule_instructors.user_id` -> `users.id`
- `schedule_student_groups.schedule_id` -> `schedules.id`
- `schedule_student_groups.student_group_id` -> `student_groups.id`
- `room_activity_types.room_id` -> `rooms.id`
- `room_activity_types.activity_type_id` -> `activity_types.id`

## Important Design Decisions

- Recurring schedules are handled at application level by generating independent rows in `schedules`.
- No `recurrence_rule` or `parent_schedule_id` column is used.
- `schedules.status` values are `draft`, `pending_approval`, `approved`, `revised`, and `cancelled`.
- `room_activity_types` uses a composite primary key: `room_id` + `activity_type_id`.
- `course_offerings.is_practicum` and `course_offerings.settings` were removed from the current schema.

## Documentation Notes

- `DataDictionary.md` is not fully synced with the latest migrations yet.
- A full Data Dictionary rewrite should be handled as a separate documentation task.
