<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lead extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'full_name', 'email', 'phone', 'country_code',
        'property_type', 'product_interest', 'source',
        'status', 'priority', 'notes', 'contacted_at', 'assigned_to',
    ];

    protected $casts = [
        'contacted_at' => 'datetime',
    ];

    public function assignedUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'assigned_to');
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'new'       => '#e95a54',
            'contacted' => '#fbcdab',
            'qualified' => '#8f9793',
            'lost'      => '#2a273c',
            default     => '#f2f0eb',
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'high'   => '#e95a54',
            'medium' => '#fbcdab',
            'low'    => '#8f9793',
            default  => '#f2f0eb',
        };
    }

    public static function getProductLabel(string $key): string
    {
        return match($key) {
            'fastpass'      => 'FastPass',
            'signstay'      => 'SignStay',
            'swiftcheckin'  => 'Swift Check-In',
            'both'          => 'All Products',
            default         => ucfirst($key),
        };
    }

    public static function getPropertyLabel(string $key): string
    {
        return match($key) {
            'hotel'               => 'Hotel',
            'serviced-apartments' => 'Serviced Apartments',
            'resort'              => 'Resort',
            'boutique-hotel'      => 'Boutique Hotel',
            'other'               => 'Other',
            default               => ucfirst($key),
        };
    }
}
