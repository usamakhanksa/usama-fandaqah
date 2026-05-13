<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\MultiTenant;
use App\Services\Finance\InvoiceService;
use App\Services\Finance\ZatcaService;

class Invoice extends Model
{
    use HasFactory, SoftDeletes, MultiTenant;

    protected $fillable = [
        'team_id',
        'reservation_id',
        'guest_id',
        'company_id',
        'invoice_number',
        'zatca_uuid',
        'zatca_invoice_type',
        'invoice_date',
        'due_date',
        'supply_date',
        'sub_total',
        'discount_amount',
        'discount_reason',
        'taxable_amount',
        'vat_amount',
        'vat_percentage',
        'total_amount',
        'grand_total',
        'rounding_amount',
        'currency',
        'exchange_rate',
        'payment_terms',
        'notes',
        'internal_notes',
        'status',
        'is_zatca_reported',
        'zatca_status',
        'zatca_submitted_at',
        'zatca_response',
        'zatca_xml',
        'zatca_qr_code',
        'zatca_hash',
        'zatca_previous_hash',
        'zatca_signature',
        'cancelled_at',
        'cancelled_by',
        'cancellation_reason',
        'paid_at',
        'sent_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'invoice_date' => 'datetime',
        'due_date' => 'date',
        'supply_date' => 'datetime',
        'sub_total' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'taxable_amount' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'vat_percentage' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'rounding_amount' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
        'is_zatca_reported' => 'boolean',
        'zatca_submitted_at' => 'timestamp',
        'zatca_response' => 'json',
        'cancelled_at' => 'timestamp',
        'paid_at' => 'timestamp',
        'sent_at' => 'timestamp',
    ];

    // Relationships
    public function team() { return $this->belongsTo(Team::class); }
    public function reservation() { return $this->belongsTo(Reservation::class); }
    public function guest() { return $this->belongsTo(Guest::class); }
    public function company() { return $this->belongsTo(Company::class); }
    public function items() { return $this->hasMany(InvoiceItem::class); }
    public function taxes() { return $this->hasMany(InvoiceTax::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function canceller() { return $this->belongsTo(User::class, 'cancelled_by'); }

    /**
     * Recalculate totals from items.
     */
    public function calculateTotals(): void
    {
        $items = $this->items;
        $this->sub_total = $items->sum('sub_total');
        $this->discount_amount = $items->sum('discount_amount');
        $this->taxable_amount = $items->sum('taxable_amount');
        $this->vat_amount = $items->sum('vat_amount');
        $this->total_amount = $items->sum('total_amount');
        
        // Simple rounding to nearest 0.05 or similar if needed, here just basic
        $this->grand_total = round($this->total_amount, 2);
        $this->rounding_amount = $this->grand_total - $this->total_amount;
        
        $this->save();
    }

    public function isEditable(): bool
    {
        return $this->status === 'draft';
    }

    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function markAsPaid(): void
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }

    public function cancel($userId, $reason): void
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_by' => $userId,
            'cancellation_reason' => $reason,
        ]);
    }

    // ZATCA Logic
    public function generateZatcaXml(): string
    {
        return (new ZatcaService())->generateXml($this, $this->zatca_invoice_type);
    }

    public function generateQrCode(): string
    {
        return (new ZatcaService())->generateTlvQrCode($this);
    }

    public function toZatcaArray(): array
    {
        return [
            'uuid' => $this->zatca_uuid,
            'invoice_number' => $this->invoice_number,
            'issue_date' => $this->invoice_date->format('Y-m-d'),
            'issue_time' => $this->invoice_date->format('H:i:s'),
            'invoice_type' => $this->zatca_invoice_type,
            'total_amount' => $this->grand_total,
            'vat_amount' => $this->vat_amount,
            // ... more fields as required by ZATCA API
        ];
    }
}
