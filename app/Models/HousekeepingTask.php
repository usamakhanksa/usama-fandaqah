<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HousekeepingTask extends Model {
    use HasFactory;

    protected $fillable = ['room_id', 'assigned_to', 'task_type', 'status', 'notes', 'scheduled_at', 'completed_at'];
    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function room() {
        return $this->belongsTo(Room::class);
    }
}
