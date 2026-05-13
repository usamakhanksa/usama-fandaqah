<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvoiceTransfer extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'transfer_date' => 'date',
        'amount' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'completed_at' => 'datetime',
        'reversed_at' => 'datetime',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function fromInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'from_invoice_id');
    }

    public function toInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'to_invoice_id');
    }

    public function fromGuest(): BelongsTo
    {
        return $this->belongsTo(Guest::class, 'from_guest_id');
    }

    public function toGuest(): BelongsTo
    {
        return $this->belongsTo(Guest::class, 'to_guest_id');
    }

    public function fromCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'from_company_id');
    }

    public function toCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'to_company_id');
    }

    public function fromReservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class, 'from_reservation_id');
    }

    public function toReservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class, 'to_reservation_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceTransferItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejecter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }
}
