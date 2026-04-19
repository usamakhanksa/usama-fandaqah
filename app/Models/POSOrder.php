<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class POSOrder extends Model
{
    protected $table = 'p_o_s_orders';
    protected $guarded = [];

    protected $casts = [
        'amount' => 'float',
        'subtotal' => 'float',
        'tax_amount' => 'float',
        'discount_amount' => 'float',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(POSOrderItem::class, 'p_o_s_order_id');
    }
}
