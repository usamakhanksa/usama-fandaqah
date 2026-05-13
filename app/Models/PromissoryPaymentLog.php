<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromissoryPaymentLog extends Model
{
    protected $table = 'promissory_payment_log';

    protected $fillable = [
        'promissory_id',
        'transaction_id',
        'team_id',
        'amount_applied',
        'payment_type',
        'applied_at',
        'applied_by',
        'is_reversed',
        'notes',
    ];

    protected $casts = [
        'applied_at' => 'datetime',
        'amount_applied' => 'decimal:2',
        'is_reversed' => 'boolean',
    ];

    public function promissory()
    {
        return $this->belongsTo(Promissory::class);
    }

    public function team()
    {
        return $this->belongsTo(\App\Team::class);
    }

    public function appliedBy()
    {
        return $this->belongsTo(\App\User::class, 'applied_by');
    }
}
