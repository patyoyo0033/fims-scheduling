<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'room_code',
        'room_name',
        'capacity',
        'location_type',
        'is_active',
    ];

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}
