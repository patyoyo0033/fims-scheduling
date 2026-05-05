<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseOffering extends Model
{
    protected $fillable = [
        'course_id',
        'academic_year_id',
        'coordinator_id',
        'is_practicum',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'is_practicum' => 'boolean',
            'settings'     => 'array',   // JSON ↔ PHP Array อัตโนมัติ
        ];
    }

    // ─── Relationships ────────────────────────────────────────────

    /**
     * รายวิชาที่เปิดสอน
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * ปีการศึกษาที่เปิดสอน
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * อาจารย์ผู้ประสานงานรายวิชา
     */
    public function coordinator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coordinator_id');
    }
}
