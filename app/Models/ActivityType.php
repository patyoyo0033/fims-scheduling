<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActivityType extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $casts = [
        'settings' => 'array',
        'is_practicum' => 'boolean',
    ];
}
