<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'room_code',
        'room_name',
        'capacity',
        'location_type_id',
        'equipment_type',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'capacity' => 'integer',
            'equipment_type' => 'array',
        ];
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function locationType()
    {
        return $this->belongsTo(LocationType::class);
    }
}
