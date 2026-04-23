<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model {
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reservation_id', 'guest_id', 'room_id',
        'reference_code', 'guest_name', 'guest_phone', 'property_reference',
        'check_in', 'check_out', 'status', 'total_amount', 'notes'
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'total_amount' => 'decimal:2'
    ];

    public function scopeSearch($query, $search) {
        return $query->where('reference_code', 'like', "%{$search}%")
                     ->orWhere('guest_name', 'like', "%{$search}%")
                     ->orWhere('property_reference', 'like', "%{$search}%");
    }
}
