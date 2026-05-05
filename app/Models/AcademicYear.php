<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYear extends Model
{
    protected $fillable = [
        'name',
        'semester',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date'   => 'date',
            'is_active'  => 'boolean',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────

    /**
     * กลุ่มนักศึกษาทั้งหมดในปีการศึกษานี้
     */
    public function studentGroups(): HasMany
    {
        return $this->hasMany(StudentGroup::class);
    }

    /**
     * รายวิชาที่เปิดสอนทั้งหมดในปีการศึกษานี้
     */
    public function courseOfferings(): HasMany
    {
        return $this->hasMany(CourseOffering::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────

    /**
     * แสดงชื่อปีการศึกษาแบบเต็ม เช่น "2569 ภาคเรียนที่ 1"
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->name} ภาคเรียนที่ {$this->semester}";
    }
}
