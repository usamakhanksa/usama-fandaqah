<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingBlock extends Model {
    use HasFactory;

    protected $fillable = ['property_reference', 'start_date', 'end_date', 'reason', 'notes'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];
}
