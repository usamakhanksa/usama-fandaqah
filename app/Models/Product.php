<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $guarded = [];

    protected $casts = [
        'price' => 'float',
        'old_price' => 'float',
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(ProductSubCategory::class, 'product_sub_category_id');
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(POSChannel::class, 'p_o_s_channel_id');
    }
}
