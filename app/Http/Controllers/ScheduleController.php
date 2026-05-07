<?php

namespace App\Http\Controllers;

use App\Models\CourseOffering;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\StudentGroup;
use App\Models\User;
use App\Models\ActivityType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ScheduleController extends Controller
{
    /**
     * Display a listing of schedules.
     */
    public function index()
    {
        $schedules = Schedule::with(['courseOffering.course', 'instructors', 'room', 'studentGroups'])
            ->orderBy('teaching_date', 'desc')
            ->get();

        return Inertia::render('Schedules/Index', [
            'schedules'  => $schedules,
            'offerings'  => CourseOffering::with(['course', 'academicYear'])->get(),
            'teachers'   => User::orderBy('name')->get(),
            'rooms'      => Room::where('is_active', true)->get(),
            'groups'     => StudentGroup::with('academicYear')->get(),
            'activities' => ActivityType::all(),
        ]);
    }

    /**
     * Store a newly created schedule in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_offering_id' => 'required|exists:course_offerings,id',
            'activity_type_id'   => 'required|exists:activity_types,id',
            'user_id'            => 'required|exists:users,id',
            'teaching_date'      => 'required|date',
            'start_time'         => 'required|date_format:H:i',
            'end_time'           => 'required|date_format:H:i|after:start_time',
            'is_recurring'       => 'boolean',
            'repeat_weeks'       => 'required_if:is_recurring,true|integer|min:1|max:16',
            'is_rotation'        => 'boolean',
            'room_id'            => 'exclude_if:is_rotation,true|required_if:is_rotation,false|exists:rooms,id',
            'student_group_id'   => 'exclude_if:is_rotation,true|required_if:is_rotation,false|exists:student_groups,id',
            'rotations'          => 'exclude_if:is_rotation,false|required_if:is_rotation,true|array|min:1',
            'rotations.*.room_id'          => 'exclude_if:is_rotation,false|required|exists:rooms,id',
            'rotations.*.student_group_id' => 'exclude_if:is_rotation,false|required|exists:student_groups,id',
        ]);

        $dates = [];
        $currentDate = Carbon::parse($request->teaching_date);
        $weeks = $request->is_recurring ? $request->repeat_weeks : 1;

        for ($i = 0; $i < $weeks; $i++) {
            $dates[] = $currentDate->copy()->addWeeks($i)->format('Y-m-d');
        }

        // --- 1. Build Proposed Schedules for Validation ---
        $proposed = [];
        if ($request->is_rotation) {
            foreach ($dates as $dateStr) {
                foreach ($request->rotations as $index => $rotation) {
                    $proposed[] = [
                        'date'             => $dateStr,
                        'start_time'       => $request->start_time,
                        'end_time'         => $request->end_time,
                        'room_id'          => $rotation['room_id'],
                        'student_group_id' => $rotation['student_group_id'],
                        'user_id'          => $request->user_id,
                        'is_rotation'      => true,
                        'rotation_index'   => $index,
                    ];
                }
            }
        } else {
            foreach ($dates as $dateStr) {
                $proposed[] = [
                    'date'             => $dateStr,
                    'start_time'       => $request->start_time,
                    'end_time'         => $request->end_time,
                    'room_id'          => $request->room_id,
                    'student_group_id' => $request->student_group_id,
                    'user_id'          => $request->user_id,
                    'is_rotation'      => false,
                ];
            }
        }

        // --- 2. Capacity & Conflict Validation ---
        foreach ($proposed as $p) {
            $room = Room::find($p['room_id']);
            $group = StudentGroup::find($p['student_group_id']);

            // Capacity Check
            if ($group->student_count > $room->capacity) {
                $errorKey = $p['is_rotation'] ? "rotations.{$p['rotation_index']}.room_id" : 'room_id';
                throw ValidationException::withMessages([
                    $errorKey => "ห้อง {$room->room_code} ความจุไม่พอสำหรับกลุ่ม {$group->group_name} (ต้องการ {$group->student_count} คน แต่รับได้ {$room->capacity} คน)"
                ]);
            }

            // Fetch Overlapping Schedules in DB
            $overlappingSchedules = Schedule::with(['instructors', 'studentGroups'])
                ->where('teaching_date', $p['date'])
                ->where('start_time', '<', $p['end_time'])
                ->where('end_time', '>', $p['start_time'])
                ->get();

            foreach ($overlappingSchedules as $existing) {
                $timeConflictMsg = "ในวันที่ " . Carbon::parse($p['date'])->format('d/m/Y') . " เวลา " . substr($existing->start_time, 0, 5) . "-" . substr($existing->end_time, 0, 5);
                
                // Room Conflict
                if ($existing->room_id == $p['room_id']) {
                    $errorKey = $p['is_rotation'] ? "rotations.{$p['rotation_index']}.room_id" : 'room_id';
                    throw ValidationException::withMessages([
                        $errorKey => "ห้อง {$room->room_code} มีการใช้งานแล้ว {$timeConflictMsg}"
                    ]);
                }

                // Instructor Conflict
                if ($existing->instructors->contains('id', $p['user_id'])) {
                    $teacher = User::find($p['user_id']);
                    throw ValidationException::withMessages([
                        'user_id' => "อาจารย์ {$teacher->name} ติดสอนตารางอื่น {$timeConflictMsg}"
                    ]);
                }

                // Student Group Conflict
                if ($existing->studentGroups->contains('id', $p['student_group_id'])) {
                    $errorKey = $p['is_rotation'] ? "rotations.{$p['rotation_index']}.student_group_id" : 'student_group_id';
                    throw ValidationException::withMessages([
                        $errorKey => "กลุ่มนักศึกษา {$group->group_name} มีเรียนวิชาอื่น {$timeConflictMsg}"
                    ]);
                }
            }
        }

        // --- 3. Self-Conflict Check for Proposed Rotations ---
        // If it's a rotation, ensure the same room or group isn't selected twice in the same time slot
        if ($request->is_rotation) {
            $roomIds = array_column($request->rotations, 'room_id');
            if (count($roomIds) !== count(array_unique($roomIds))) {
                throw ValidationException::withMessages([
                    'rotations' => 'มีการเลือกห้องเรียนซ้ำซ้อนในเวลาเดียวกัน'
                ]);
            }
            $groupIds = array_column($request->rotations, 'student_group_id');
            if (count($groupIds) !== count(array_unique($groupIds))) {
                throw ValidationException::withMessages([
                    'rotations' => 'มีการเลือกกลุ่มนักศึกษาซ้ำซ้อนในเวลาเดียวกัน'
                ]);
            }
        }

        // --- 4. Database Insert ---
        DB::transaction(function () use ($request, $dates) {
            if ($request->is_rotation) {
                foreach ($dates as $dateStr) {
                    foreach ($request->rotations as $rotation) {
                        $schedule = Schedule::create([
                            'course_offering_id' => $request->course_offering_id,
                            'activity_type_id'   => $request->activity_type_id,
                            'room_id'            => $rotation['room_id'],
                            'teaching_date'      => $dateStr,
                            'start_time'         => $request->start_time,
                            'end_time'           => $request->end_time,
                            'status'             => 'draft',
                        ]);
                        $schedule->instructors()->attach($request->user_id, ['is_lead' => true]);
                        $schedule->studentGroups()->attach($rotation['student_group_id']);
                    }
                }
            } else {
                foreach ($dates as $dateStr) {
                    $schedule = Schedule::create([
                        'course_offering_id' => $request->course_offering_id,
                        'activity_type_id'   => $request->activity_type_id,
                        'room_id'            => $request->room_id,
                        'teaching_date'      => $dateStr,
                        'start_time'         => $request->start_time,
                        'end_time'           => $request->end_time,
                        'status'             => 'draft',
                    ]);
                    $schedule->instructors()->attach($request->user_id, ['is_lead' => true]);
                    $schedule->studentGroups()->attach($request->student_group_id);
                }
            }
        });

        return redirect()->back()->with('success', 'บันทึกตารางสอนสำเร็จแล้ว');
    }
}
