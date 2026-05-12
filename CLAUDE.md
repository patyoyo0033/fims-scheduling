# CLAUDE.md — FIMS Scheduling System

## Project Overview

ระบบจัดตารางสอน คณะพยาบาลศาสตร์ มหาวิทยาลัยมหิดล (FIMS Scheduling).
Internal scheduling tool for the Faculty of Nursing, designed to handle **recurring weekly classes** and **practicum group rotation** across rooms and wards.

## Tech Stack

| Layer      | Technology                                                |
| ---------- | --------------------------------------------------------- |
| Backend    | **Laravel 13** (PHP 8.3+)                                 |
| Frontend   | **Vue 3** (`<script setup>`) + **Inertia.js v2**          |
| Styling    | **Tailwind CSS 3** with custom `nursing` color palette    |
| Auth       | Laravel Breeze (Vue variant)                              |
| Roles      | Spatie Laravel-Permission                                 |
| DB         | MySQL (via `.env`)                                        |
| Build      | Vite 8, `laravel-vite-plugin`                             |
| Animations | GSAP 3 + `@vueuse/core`                                  |

## Common Commands

```bash
# Start dev servers (Laravel + Vite)
php artisan serve
npm run dev

# Or use the combined script
composer dev

# Database
php artisan migrate
php artisan db:seed          # Seeds Users, AcademicYears, Rooms, Courses, StudentGroups, CourseOfferings

# Build for production
npm run build

# Run tests
composer test
```

## Project Structure (Key Directories)

```
app/
├─ Http/Controllers/
│  ├─ ScheduleController.php        # Core scheduling logic (Recurring + Rotation)
│  ├─ MasterDataController.php      # Rooms & Student Groups CRUD
│  ├─ CourseOfferingController.php   # Course-to-semester assignment
│  └─ Auth/                         # Breeze auth controllers
├─ Models/
│  ├─ Schedule.php
│  ├─ Course.php
│  ├─ CourseOffering.php
│  ├─ Room.php
│  ├─ StudentGroup.php
│  ├─ AcademicYear.php
│  └─ User.php
database/
├─ migrations/                      # 10 migration files
├─ seeders/DatabaseSeeder.php       # Full seed with realistic Thai nursing data
resources/js/
├─ Layouts/AuthenticatedLayout.vue  # Main nav (Soft Glassmorphism design)
├─ Pages/
│  ├─ Dashboard.vue                 # Stats cards + quick actions
│  ├─ Schedules/
│  │  ├─ Index.vue                  # Schedule list + create modal trigger
│  │  └─ ScheduleForm.vue           # Smart modal (Recurring + Rotation)
│  ├─ Courses/Offerings.vue         # Course offering management
│  ├─ MasterData/Index.vue          # Rooms & groups management
│  └─ Auth/                         # Login, Register pages
routes/web.php                      # All route definitions
tailwind.config.js                  # Custom `nursing` color palette + glassmorphism tokens
```

## Design System

- **Theme**: "Soft Glassmorphism" — translucent white cards, blurred backgrounds, floating orbs
- **Primary palette**: `nursing-50` (#F0F8FF) through `nursing-900` (#1E293B)
- **Fonts**: Inter + Noto Sans Thai (via Google Fonts)
- **Shadows**: `shadow-glass`, `shadow-glass-hover`, `shadow-card-float`, `shadow-card-float-hover`
- **Animations**: `animate-float-slow`, `animate-float-medium`, `animate-float-fast`, `animate-pulse-soft`
- **All UI text is in Thai** (ภาษาไทย). Validation messages, labels, and navigation are localized.

## Architecture Decisions

1. **Inertia.js SPA** — No separate API; controllers return `Inertia::render()` with props.
2. **`useForm` for all mutations** — Vue forms use `@inertiajs/vue3` `useForm()` for state + submission.
3. **Per-row schedule creation** - `ScheduleController::store()` creates independent `schedules` rows, including one row per recurring occurrence.
4. **`exclude_if` validation** — Conditional validation rules using `exclude_if:is_rotation,true/false` to cleanly separate standard vs. rotation form data.
5. **Recurring schedules at application level** - recurring entries are expanded into individual `schedules` rows; there is no `recurrence_rule` column.
6. **Cascade rules** — Deleting a Course cascades to CourseOfferings; deleting a User nullifies `coordinator_id`.

## Database Schema (Scheduling domain)

```
users ──────────────────┐
                        │ coordinator_id (nullable)
academic_years ─────┐   │
                    │   │
student_groups ─────┤   │  (academic_year_id FK)
                    │   │
courses ────────────┤   │
                    │   │
course_offerings ───┘───┘  (course_id + academic_year_id + coordinator_id FKs)
    │
    ▼
schedules  (course_offering_id FK, activity_type_id FK, practicum_series_id FK, room_id FK)
    │
rooms ──────────────────┘
```

`schedules.status` enum values are `['draft', 'pending_approval', 'approved', 'revised', 'cancelled']`.

## Route Map

| Method   | URI                                     | Controller Method                          | Purpose                    |
| -------- | --------------------------------------- | ------------------------------------------ | -------------------------- |
| GET      | `/schedules`                            | `ScheduleController@index`                 | List all schedules         |
| POST     | `/schedules`                            | `ScheduleController@store`                 | Create schedule(s)         |
| POST     | `/schedules/check-conflict`             | `ScheduleController@checkConflict`         | (Mock) conflict check      |
| GET      | `/master-data`                          | `MasterDataController@index`               | Rooms + groups page        |
| POST     | `/master-data/rooms`                    | `MasterDataController@storeRoom`           | Create room                |
| PUT      | `/master-data/rooms/{room}`             | `MasterDataController@updateRoom`          | Update room                |
| DELETE   | `/master-data/rooms/{room}`             | `MasterDataController@destroyRoom`         | Delete room                |
| POST     | `/master-data/student-groups`           | `MasterDataController@storeStudentGroup`   | Create group               |
| PUT      | `/master-data/student-groups/{sg}`      | `MasterDataController@updateStudentGroup`  | Update group               |
| DELETE   | `/master-data/student-groups/{sg}`      | `MasterDataController@destroyStudentGroup` | Delete group               |
| GET      | `/course-offerings`                     | `CourseOfferingController@index`           | List offerings             |
| POST     | `/course-offerings`                     | `CourseOfferingController@store`           | Create offering            |
| PUT      | `/course-offerings/{co}`                | `CourseOfferingController@update`          | Update offering            |
| DELETE   | `/course-offerings/{co}`                | `CourseOfferingController@destroy`         | Delete offering            |

## Known Limitations / Technical Debt

1. **No real conflict detection** — `checkConflict` is a mock; overlapping times/rooms/teachers are not validated.
2. **Rotation doesn't truly rotate** — Recurring + Rotation copies the same room-group pairs to all weeks (no Shift/Swap logic).
3. **No capacity check** — Student group size is not compared against room capacity.
4. **No Edit/Delete for schedules** — Only Create exists; update/destroy methods are not implemented.
5. **Foreign key delete risk** — Deleting a Room/Group used in schedules triggers a raw 500 error (no try/catch).
6. **No UI for Courses or Academic Years** — These are seeder-only; no admin CRUD pages exist.
7. **Data Dictionary is not fully synced yet** - `DataDictionary.md` still needs a dedicated pass to match the latest migrations.

## Testing

Scheduling database tests must run on MySQL, not SQLite, because the migrations use MySQL-specific `ALTER TABLE ... MODIFY` syntax for enum changes.

Default seeder credentials:
- **Admin**: `admin@fims.com` / `password`
- **Teacher 1**: `teacher1@fims.com` / `password`
- **Teacher 2**: `teacher2@fims.com` / `password`

## Git Branching Convention

- `main` — stable, merged code
- `feature/sprint{N}-{module-name}` — e.g., `feature/sprint4-schedule-management`
- `feature/{descriptive-name}` — e.g., `feature/database-seeder`
- **Rule**: Never merge to `main` without explicit user approval.
