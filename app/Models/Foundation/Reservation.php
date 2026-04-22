<?php

namespace App\Models\Foundation;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Database\Factories\Foundation\ReservationFactory;

class Reservation extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'pms_reservations';

    protected $fillable = [
        'reservation_no','customer_id','unit_id','check_in_date','check_out_date','adults','children','status','night_rate_sar','nights','subtotal_sar','tax_sar','total_sar','source','confirmed_at','special_requests',
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'night_rate_sar' => 'decimal:2',
        'subtotal_sar' => 'decimal:2',
        'tax_sar' => 'decimal:2',
        'total_sar' => 'decimal:2',
        'confirmed_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    protected static function newFactory(): ReservationFactory
    {
        return ReservationFactory::new();
    }
}
