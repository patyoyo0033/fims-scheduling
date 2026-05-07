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
