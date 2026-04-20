<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'team_id',
        'customer_id',
        'unit_id',
        'status',
        'number',
        'date_in',
        'date_out',
        'sub_total',
        'vat_total',
        'total_price',
        'business_date',
    ];

    protected $casts = [
        'date_in' => 'date',
        'date_out' => 'date',
        'business_date' => 'date',
        'sub_total' => 'decimal:2',
        'vat_total' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
