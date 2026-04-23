<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomRestriction extends Model {
    use HasFactory;

    protected $fillable = ['room_id', 'start_date', 'end_date', 'restriction_type', 'value', 'reason'];
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function room() {
        return $this->belongsTo(Room::class);
    }
}
