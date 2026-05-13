<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceTransferItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function invoiceTransfer(): BelongsTo
    {
        return $this->belongsTo(InvoiceTransfer::class);
    }

    public function fromItem(): BelongsTo
    {
        return $this->belongsTo(InvoiceItem::class, 'from_invoice_item_id');
    }

    public function toItem(): BelongsTo
    {
        return $this->belongsTo(InvoiceItem::class, 'to_invoice_item_id');
    }
}
