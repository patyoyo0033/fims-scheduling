<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\CourseOffering;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CourseOfferingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Courses/Offerings', [
            // Get all offerings with their relationships loaded
            'offerings' => CourseOffering::with(['course', 'academicYear', 'coordinator'])
                ->latest()
                ->get(),
            // Master data for the dropdowns
            'courses' => Course::orderBy('course_code')->get(),
            'academicYears' => AcademicYear::latest()->get(),
            'coordinators' => User::orderBy('name')->get(), // In real app, might filter by role (e.g., teacher)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'coordinator_id' => 'nullable|exists:users,id',
            'is_practicum' => 'boolean',
            'max_students' => 'nullable|integer|min:1|required_if:is_practicum,true',
        ]);

        // Prevent exact same course in the same academic year
        $request->validate([
            'course_id' => [
                function ($attribute, $value, $fail) use ($request) {
                    $exists = CourseOffering::where('course_id', $value)
                        ->where('academic_year_id', $request->academic_year_id)
                        ->exists();
                    if ($exists) {
                        $fail('รายวิชานี้ถูกเปิดสอนในปีการศึกษานี้ไปแล้ว');
                    }
                },
            ],
        ]);

        // Build settings JSON
        $settings = null;
        if ($request->is_practicum && $request->max_students) {
            $settings = [
                'max_students_per_group' => $request->max_students,
            ];
        }

        CourseOffering::create([
            'course_id' => $validated['course_id'],
            'academic_year_id' => $validated['academic_year_id'],
            'coordinator_id' => $validated['coordinator_id'] ?? null,
            'is_practicum' => $validated['is_practicum'] ?? false,
            'settings' => $settings,
        ]);

        return redirect()->back()->with('success', 'เปิดรายวิชาสำเร็จ');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CourseOffering $courseOffering)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'coordinator_id' => 'nullable|exists:users,id',
            'is_practicum' => 'boolean',
            'max_students' => 'nullable|integer|min:1|required_if:is_practicum,true',
        ]);

        // Prevent exact same course in the same academic year, ignoring self
        $request->validate([
            'course_id' => [
                function ($attribute, $value, $fail) use ($request, $courseOffering) {
                    $exists = CourseOffering::where('course_id', $value)
                        ->where('academic_year_id', $request->academic_year_id)
                        ->where('id', '!=', $courseOffering->id)
                        ->exists();
                    if ($exists) {
                        $fail('รายวิชานี้ถูกเปิดสอนในปีการศึกษานี้ไปแล้ว');
                    }
                },
            ],
        ]);

        // Build settings JSON
        $settings = null;
        if ($request->is_practicum && $request->max_students) {
            $settings = [
                'max_students_per_group' => $request->max_students,
            ];
        }

        $courseOffering->update([
            'course_id' => $validated['course_id'],
            'academic_year_id' => $validated['academic_year_id'],
            'coordinator_id' => $validated['coordinator_id'] ?? null,
            'is_practicum' => $validated['is_practicum'] ?? false,
            'settings' => $settings,
        ]);

        return redirect()->back()->with('success', 'อัปเดตรายวิชาที่เปิดสอนสำเร็จ');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CourseOffering $courseOffering)
    {
        try {
            $courseOffering->delete();
            return redirect()->back()->with('success', 'ลบรายวิชาที่เปิดสอนสำเร็จ');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->errorInfo[1] == 1451 || $e->getCode() == '23000') {
                return redirect()->back()->with('error', 'ไม่สามารถลบข้อมูลได้ เนื่องจากข้อมูลนี้ถูกใช้งานอยู่ในระบบแล้ว');
            }
            throw $e;
        }
    }
}
