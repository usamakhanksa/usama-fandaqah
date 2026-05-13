<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\MultiTenant;

class InvoiceItem extends Model
{
    use HasFactory, MultiTenant;

    protected $fillable = [
        'invoice_id',
        'team_id',
        'product_name',
        'product_name_ar',
        'description',
        'quantity',
        'unit_price',
        'sub_total',
        'discount_amount',
        'discount_reason',
        'taxable_amount',
        'vat_amount',
        'vat_percentage',
        'total_amount',
        'item_type',
        'reference_type',
        'reference_id',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'sub_total' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'taxable_amount' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'vat_percentage' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }

    /**
     * Auto-calculate values on saving.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            $item->sub_total = $item->quantity * $item->unit_price;
            $item->taxable_amount = $item->sub_total - $item->discount_amount;
            $item->vat_amount = round($item->taxable_amount * ($item->vat_percentage / 100), 2);
            $item->total_amount = $item->taxable_amount + $item->vat_amount;
        });
    }
}
