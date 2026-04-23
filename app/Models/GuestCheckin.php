<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GuestCheckin extends Model {
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'booking_reference', 'guest_name', 'room_number', 'expected_arrival_date',
        'expected_departure_date', 'actual_arrival_time', 'actual_departure_time',
        'status', 'id_verified', 'deposit_collected', 'notes'
    ];

    protected $casts = [
        'expected_arrival_date' => 'date',
        'expected_departure_date' => 'date',
        'actual_arrival_time' => 'datetime',
        'actual_departure_time' => 'datetime',
        'id_verified' => 'boolean',
        'deposit_collected' => 'decimal:2',
    ];

    public function scopeSearch($query, $search) {
        return $query->where('guest_name', 'like', "%{$search}%")
                     ->orWhere('room_number', 'like', "%{$search}%")
                     ->orWhere('booking_reference', 'like', "%{$search}%");
    }
}
