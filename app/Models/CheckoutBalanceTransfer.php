<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheckoutBalanceTransfer extends Model
{
    public $timestamps = false;

    protected $table = 'checkout_balance_transfers';

    protected $fillable = [
        'team_id',
        'reservation_id',
        'transfer_type',
        'amount',
        'promissory_id',
        'refund_transaction_id',
        'transferred_by',
        'notes',
    ];

    protected $casts = ['transferred_at' => 'datetime'];

    public function reservation() { return $this->belongsTo(Reservation::class); }
    public function team()        { return $this->belongsTo(\App\Models\Team::class); }
    public function transferredBy(){ return $this->belongsTo(\App\User::class, 'transferred_by'); }
}
