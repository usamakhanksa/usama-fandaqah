<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingEvent extends Model {
    use HasFactory;

    protected $fillable = ['title', 'property_reference', 'start_time', 'end_time', 'type', 'description'];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];
}
