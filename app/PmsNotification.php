<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PmsNotification extends Model
{
    protected $fillable = [
        'team_id',
        'type',
        'subtype',
        'treated_by',
        'guest_name',
        'room_number',
        'is_treated',
        'scheduled_at'
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'read_at' => 'datetime',
    ];

  
}

