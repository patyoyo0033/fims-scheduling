<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = [
        'course_offering_id',
        'activity_type_id',
        'practicum_series_id',
        'room_id',
        'topic',
        'remark',
        'teaching_date',
        'start_time',
        'end_time',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'teaching_date' => 'string',
        ];
    }

    public function courseOffering()
    {
        return $this->belongsTo(CourseOffering::class);
    }

    public function activityType()
    {
        return $this->belongsTo(ActivityType::class);
    }

    public function practicumSeries()
    {
        return $this->belongsTo(PracticumSeries::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function instructors()
    {
        return $this->belongsToMany(User::class, 'schedule_instructors')->withPivot('is_lead')->withTimestamps();
    }

    public function studentGroups()
    {
        return $this->belongsToMany(StudentGroup::class, 'schedule_student_groups')->withTimestamps();
    }
}
