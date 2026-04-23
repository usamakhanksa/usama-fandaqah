<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model {
    use HasFactory, SoftDeletes;

    protected $fillable = ['room_number', 'room_type_id', 'type', 'floor', 'capacity', 'base_price', 'status', 'features'];
    protected $casts = [
        'base_price' => 'decimal:2',
        'features' => 'array',
    ];

    public function housekeepingTasks() {
        return $this->hasMany(HousekeepingTask::class);
    }

    public function roomType() {
        return $this->belongsTo(RoomType::class);
    }

    public function restrictions() {
        return $this->hasMany(RoomRestriction::class);
    }

    public function scopeSearch($query, $search) {
        return $query->where('room_number', 'like', "%{$search}%")
                     ->orWhere('type', 'like', "%{$search}%");
    }
}
