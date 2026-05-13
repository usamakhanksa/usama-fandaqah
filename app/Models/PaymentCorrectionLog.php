<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentCorrectionLog extends Model
{
    protected $guarded = [];

    protected $casts = [
        'original_amount'    => 'decimal:2',
        'correct_amount'     => 'decimal:2',
        'posted_business_date' => 'date',
    ];

    public function correctionWithdraw()
    {
        return $this->belongsTo(\App\Transaction::class, 'correction_withdraw_id');
    }

    public function correctionDeposit()
    {
        return $this->belongsTo(\App\Transaction::class, 'correction_deposit_id');
    }

    public function frozenTransaction()
    {
        return $this->belongsTo(\App\Transaction::class, 'frozen_transaction_id');
    }

    public function creator()
    {
        return $this->belongsTo(\App\User::class, 'created_by');
    }
}
