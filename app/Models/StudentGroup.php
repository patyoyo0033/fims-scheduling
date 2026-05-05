<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentGroup extends Model
{
    protected $fillable = [
        'group_name',
        'year_level',
        'student_count',
        'academic_year_id',
    ];

    protected function casts(): array
    {
        return [
            'year_level'    => 'integer',
            'student_count' => 'integer',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────

    /**
     * ปีการศึกษาที่กลุ่มนี้สังกัด
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
