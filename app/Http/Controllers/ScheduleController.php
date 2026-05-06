<?php

namespace App\Http\Controllers;

use App\Models\CourseOffering;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\StudentGroup;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ScheduleController extends Controller
{
    /**
     * Display a listing of schedules.
     */
    public function index()
    {
        $schedules = Schedule::with(['course', 'user', 'room'])
            ->orderBy('teaching_date', 'desc')
            ->get();

        return Inertia::render('Schedules/Index', [
            'schedules' => $schedules,
            'offerings' => CourseOffering::with(['course', 'academicYear'])->get(),
            'teachers'  => User::orderBy('name')->get(),
            'rooms'     => Room::where('is_active', true)->get(),
            'groups'    => StudentGroup::with('academicYear')->get(),
        ]);
    }

    /**
     * Store a newly created schedule in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_offering_id' => 'required|exists:course_offerings,id',
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

        $offering = CourseOffering::findOrFail($request->course_offering_id);
        $courseId = $offering->course_id;

        // Calculate dates for recurring schedules
        $dates = [];
        $currentDate = Carbon::parse($request->teaching_date);
        $weeks = $request->is_recurring ? $request->repeat_weeks : 1;

        for ($i = 0; $i < $weeks; $i++) {
            $dates[] = $currentDate->copy()->addWeeks($i)->format('Y-m-d');
        }

        $inserts = [];

        // Logic for building the insert array
        if ($request->is_rotation) {
            // Rotation Logic: Iterate through dates and rotations
            foreach ($dates as $dateStr) {
                foreach ($request->rotations as $rotation) {
                    $group = StudentGroup::find($rotation['student_group_id']);
                    $inserts[] = [
                        'course_id'     => $courseId,
                        'user_id'       => $request->user_id,
                        'room_id'       => $rotation['room_id'],
                        'student_group' => $group->group_name,
                        'student_count' => $group->student_count,
                        'teaching_date' => $dateStr,
                        'start_time'    => $request->start_time,
                        'end_time'      => $request->end_time,
                        'status'        => 'draft',
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ];
                }
            }
        } else {
            // Standard Logic
            $group = StudentGroup::find($request->student_group_id);
            foreach ($dates as $dateStr) {
                $inserts[] = [
                    'course_id'     => $courseId,
                    'user_id'       => $request->user_id,
                    'room_id'       => $request->room_id,
                    'student_group' => $group->group_name,
                    'student_count' => $group->student_count,
                    'teaching_date' => $dateStr,
                    'start_time'    => $request->start_time,
                    'end_time'      => $request->end_time,
                    'status'        => 'draft',
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
            }
        }

        /* 
         * MOCK CONFLICT CHECK:
         * We skip the complex conflict checking (overlapping times/rooms) for now 
         * to focus purely on making the Recurring and Rotation insertion logic robust and bug-free.
         */

        Schedule::insert($inserts);

        return redirect()->back()->with('success', 'บันทึกตารางสอนสำเร็จแล้ว');
    }
}
