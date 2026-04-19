<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class POSTransaction extends Model
{
    protected $table = 'p_o_s_transactions';
    protected $guarded = [];

    public function order(): BelongsTo
    {
        return $this->belongsTo(POSOrder::class, 'p_o_s_order_id');
    }
}
