<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommissionPaymentDetail extends Model
{
    protected $fillable = [
        'commission_payment_id',
        'reservation_id',
        'commission_rate',
        'commission_amount',
        'room_revenue',
        'fb_revenue',
        'other_revenue',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'room_revenue' => 'decimal:2',
        'fb_revenue' => 'decimal:2',
        'other_revenue' => 'decimal:2',
    ];

    public function commissionPayment()
    {
        return $this->belongsTo(CommissionPayment::class);
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}
