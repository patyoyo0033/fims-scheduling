<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ScheduleController extends Controller
{
    /**
     * แสดงรายการตารางสอนทั้งหมด
     */
    public function index()
    {
        $schedules = Schedule::with(['course', 'user', 'room'])
            ->orderBy('teaching_date', 'desc')
            ->get();

        return Inertia::render('Schedules/Index', [
            'schedules' => $schedules,
        ]);
    }

    /**
     * แสดงฟอร์มสร้างตารางสอนใหม่
     */
    public function create()
    {
        return Inertia::render('ScheduleForm', [
            'courses'  => Course::select('id', 'course_code as code', 'course_name as name')->get(),
            'teachers' => User::select('id', 'name')->get(),
            'rooms'    => Room::select('id', 'room_name as name', 'room_code as code', 'capacity')->where('is_active', true)->get(),
            'bookedSlots' => Schedule::select('user_id', 'room_id', 'teaching_date', 'start_time', 'end_time')->get(),
        ]);
    }

    /**
     * บันทึกตารางสอนใหม่ลงฐานข้อมูล
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id'     => 'required|exists:courses,id',
            'user_id'       => 'required|exists:users,id',
            'room_id'       => 'required|exists:rooms,id',
            'student_group' => 'required|string|max:100',
            'student_count' => 'required|integer|min:1',
            'teaching_date' => 'required|date',
            'start_time'    => 'required|date_format:H:i',
            'end_time'      => 'required|date_format:H:i|after:start_time',
        ]);

        // ตรวจสอบ Hard Conflict: ห้องหรืออาจารย์ซ้อนเวลา
        $conflict = Schedule::where('teaching_date', $validated['teaching_date'])
            ->where(function ($query) use ($validated) {
                $query->where('room_id', $validated['room_id'])
                      ->orWhere('user_id', $validated['user_id']);
            })
            ->where(function ($query) use ($validated) {
                $query->where('start_time', '<', $validated['end_time'])
                      ->where('end_time', '>', $validated['start_time']);
            })
            ->exists();

        if ($conflict) {
            return back()->withErrors([
                'conflict' => 'พบการซ้อนเวลา! อาจารย์หรือห้องเรียนถูกจองในช่วงเวลาดังกล่าวแล้ว',
            ]);
        }

        $validated['status'] = 'draft';
        Schedule::create($validated);

        return redirect()->route('schedules.create')
            ->with('success', 'บันทึกตารางสอนเรียบร้อยแล้ว');
    }

    /**
     * API endpoint สำหรับตรวจสอบ Conflict แบบ Real-time (AJAX)
     */
    public function checkConflict(Request $request)
    {
        $request->validate([
            'teaching_date' => 'required|date',
            'start_time'    => 'required',
            'end_time'      => 'required',
        ]);

        $conflicts = [];

        // ตรวจสอบอาจารย์ซ้อน
        if ($request->user_id) {
            $teacherConflict = Schedule::where('teaching_date', $request->teaching_date)
                ->where('user_id', $request->user_id)
                ->where('start_time', '<', $request->end_time)
                ->where('end_time', '>', $request->start_time)
                ->exists();

            if ($teacherConflict) {
                $conflicts[] = [
                    'type'    => 'error',
                    'message' => '🚨 อาจารย์ท่านนี้มีตารางสอนซ้อนในช่วงเวลาที่เลือก',
                ];
            }
        }

        // ตรวจสอบห้องซ้อน
        if ($request->room_id) {
            $roomConflict = Schedule::where('teaching_date', $request->teaching_date)
                ->where('room_id', $request->room_id)
                ->where('start_time', '<', $request->end_time)
                ->where('end_time', '>', $request->start_time)
                ->exists();

            if ($roomConflict) {
                $conflicts[] = [
                    'type'    => 'error',
                    'message' => '🚨 ห้องเรียนนี้ถูกจองแล้วในช่วงเวลาที่เลือก',
                ];
            }
        }

        // ตรวจสอบ capacity
        if ($request->room_id && $request->student_count) {
            $room = Room::find($request->room_id);
            if ($room && (int)$request->student_count > $room->capacity) {
                $conflicts[] = [
                    'type'    => 'warning',
                    'message' => "⚠️ จำนวนนักศึกษา ({$request->student_count}) เกินความจุห้อง {$room->room_name} (สูงสุด {$room->capacity} คน)",
                ];
            }
        }

        return response()->json(['conflicts' => $conflicts]);
    }
}
