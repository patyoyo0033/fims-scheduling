<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Room;
use App\Models\StudentGroup;
use App\Models\LocationType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MasterDataController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('MasterData/Index', [
            'rooms' => Room::with('locationType')->latest()->get(),
            'studentGroups' => StudentGroup::with('academicYear')->latest()->get(),
            'academicYears' => AcademicYear::latest()->get(),
            'locationTypes' => LocationType::all(),
        ]);
    }

    // ─── Room Management ───────────────────────────────────────────────────

    public function storeRoom(Request $request)
    {
        $validated = $request->validate([
            'room_code' => 'required|string|max:255|unique:rooms',
            'room_name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'location_type_id' => 'required|exists:location_types,id',
            'is_active' => 'boolean',
        ]);

        Room::create($validated);

        return redirect()->back()->with('success', 'เพิ่มข้อมูลห้องเรียนสำเร็จ');
    }

    public function updateRoom(Request $request, Room $room)
    {
        $validated = $request->validate([
            'room_code' => 'required|string|max:255|unique:rooms,room_code,' . $room->id,
            'room_name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'location_type_id' => 'required|exists:location_types,id',
            'is_active' => 'boolean',
        ]);

        $room->update($validated);

        return redirect()->back()->with('success', 'แก้ไขข้อมูลห้องเรียนสำเร็จ');
    }

    public function destroyRoom(Room $room)
    {
        try {
            $room->delete();
            return redirect()->back()->with('success', 'ลบข้อมูลห้องเรียนสำเร็จ');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->errorInfo[1] == 1451 || $e->getCode() == '23000') {
                return redirect()->back()->with('error', 'ไม่สามารถลบข้อมูลได้ เนื่องจากข้อมูลนี้ถูกใช้งานอยู่ในระบบแล้ว');
            }
            throw $e;
        }
    }

    // ─── Student Group Management ──────────────────────────────────────────

    public function storeStudentGroup(Request $request)
    {
        $validated = $request->validate([
            'group_name' => 'required|string|max:255',
            'year_level' => 'required|integer|min:1|max:6',
            'student_count' => 'required|integer|min:0',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        // Validate uniqueness of group_name + academic_year_id
        $request->validate([
            'group_name' => [
                function ($attribute, $value, $fail) use ($request) {
                    $exists = StudentGroup::where('group_name', $value)
                        ->where('academic_year_id', $request->academic_year_id)
                        ->exists();
                    if ($exists) {
                        $fail('ชื่อกลุ่มนี้มีอยู่ในปีการศึกษานี้แล้ว');
                    }
                },
            ],
        ]);

        StudentGroup::create($validated);

        return redirect()->back()->with('success', 'เพิ่มข้อมูลกลุ่มนักศึกษาสำเร็จ');
    }

    public function updateStudentGroup(Request $request, StudentGroup $studentGroup)
    {
        $validated = $request->validate([
            'group_name' => 'required|string|max:255',
            'year_level' => 'required|integer|min:1|max:6',
            'student_count' => 'required|integer|min:0',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        // Validate uniqueness of group_name + academic_year_id ignoring current group
        $request->validate([
            'group_name' => [
                function ($attribute, $value, $fail) use ($request, $studentGroup) {
                    $exists = StudentGroup::where('group_name', $value)
                        ->where('academic_year_id', $request->academic_year_id)
                        ->where('id', '!=', $studentGroup->id)
                        ->exists();
                    if ($exists) {
                        $fail('ชื่อกลุ่มนี้มีอยู่ในปีการศึกษานี้แล้ว');
                    }
                },
            ],
        ]);

        $studentGroup->update($validated);

        return redirect()->back()->with('success', 'แก้ไขข้อมูลกลุ่มนักศึกษาสำเร็จ');
    }

    public function destroyStudentGroup(StudentGroup $studentGroup)
    {
        try {
            $studentGroup->delete();
            return redirect()->back()->with('success', 'ลบข้อมูลกลุ่มนักศึกษาสำเร็จ');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->errorInfo[1] == 1451 || $e->getCode() == '23000') {
                return redirect()->back()->with('error', 'ไม่สามารถลบข้อมูลได้ เนื่องจากข้อมูลนี้ถูกใช้งานอยู่ในระบบแล้ว');
            }
            throw $e;
        }
    }
}
