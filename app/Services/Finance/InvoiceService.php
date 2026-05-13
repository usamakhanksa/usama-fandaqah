<?php

namespace App\Services\Finance;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceTax;
use App\Models\Team;
use App\Models\TeamCounter;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class InvoiceService
{
    /**
     * Create invoice with items and calculate totals.
     */
    public function createInvoice(array $data, array $items): Invoice
    {
        return DB::transaction(function () use ($data, $items) {
            $invoice = Invoice::create([
                'team_id' => $data['team_id'],
                'reservation_id' => $data['reservation_id'] ?? null,
                'guest_id' => $data['guest_id'] ?? null,
                'company_id' => $data['company_id'] ?? null,
                'invoice_number' => $this->generateInvoiceNumber($data['team_id']),
                'zatca_uuid' => \Illuminate\Support\Str::uuid(),
                'zatca_invoice_type' => $data['zatca_invoice_type'] ?? 'simplified',
                'invoice_date' => $data['invoice_date'] ?? now(),
                'due_date' => $data['due_date'] ?? null,
                'supply_date' => $data['supply_date'] ?? now(),
                'currency' => $data['currency'] ?? 'SAR',
                'exchange_rate' => $data['exchange_rate'] ?? 1.0000,
                'payment_terms' => $data['payment_terms'] ?? null,
                'notes' => $data['notes'] ?? null,
                'internal_notes' => $data['internal_notes'] ?? null,
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            foreach ($items as $index => $item) {
                InvoiceItem::create(array_merge($item, [
                    'invoice_id' => $invoice->id,
                    'team_id' => $invoice->team_id,
                    'sort_order' => $index,
                ]));
            }

            $this->updateInvoiceTotals($invoice);

            return $invoice->load(['items', 'taxes']);
        });
    }

    /**
     * Recalculates all amounts from items.
     */
    public function updateInvoiceTotals(Invoice $invoice): void
    {
        $invoice->calculateTotals();
        
        // Also update summary taxes if needed (invoice_taxes table)
        $invoice->taxes()->delete();
        $vatTotal = $invoice->items->sum('vat_amount');
        if ($vatTotal > 0) {
            InvoiceTax::create([
                'invoice_id' => $invoice->id,
                'tax_type' => 'vat',
                'tax_name' => 'VAT',
                'tax_percentage' => 15.00,
                'tax_amount' => $vatTotal,
            ]);
        }
    }

    /**
     * Thread-safe auto-incrementing invoice number.
     */
    public function generateInvoiceNumber($teamId): string
    {
        $date = now();
        $prefix = 'INV-' . $date->format('Ym') . '-';

        return DB::transaction(function () use ($teamId, $prefix) {
            $counter = TeamCounter::where('team_id', $teamId)
                ->where('type', 'invoice')
                ->lockForUpdate()
                ->first();

            if (!$counter) {
                $counter = TeamCounter::create([
                    'team_id' => $teamId,
                    'type' => 'invoice',
                    'value' => 1,
                ]);
            } else {
                $counter->increment('value');
            }

            return $prefix . str_pad($counter->value, 4, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Auto-generates invoice from reservation charges.
     */
    public function generateReservationInvoice($reservationId): Invoice
    {
        $reservation = Reservation::with(['guest', 'company'])->findOrFail($reservationId);
        
        // Placeholder for charge logic: in a real app, you'd pull from room_charges, service_logs, etc.
        $items = [
            [
                'product_name' => 'Room Charge',
                'product_name_ar' => 'رسوم الغرفة',
                'quantity' => 1,
                'unit_price' => 500, // example
                'item_type' => 'room_charge',
            ]
        ];

        return $this->createInvoice([
            'team_id' => $reservation->team_id,
            'reservation_id' => $reservation->id,
            'guest_id' => $reservation->guest_id,
            'company_id' => $reservation->company_id,
            'zatca_invoice_type' => $reservation->company_id ? 'standard' : 'simplified',
        ], $items);
    }

    /**
     * Submit to ZATCA API.
     */
    public function submitToZatca(Invoice $invoice)
    {
        $zatcaService = new ZatcaService();
        return $zatcaService->reportInvoice($invoice);
    }

    /**
     * Cancellation flow.
     */
    public function cancelInvoice(Invoice $invoice, $userId, $reason)
    {
        return DB::transaction(function () use ($invoice, $userId, $reason) {
            $invoice->cancel($userId, $reason);

            if ($invoice->is_zatca_reported) {
                // Generate Credit Note if already reported
                $this->generateCreditNote($invoice, $userId, $reason);
            }

            return $invoice;
        });
    }

    private function generateCreditNote(Invoice $invoice, $userId, $reason)
    {
        // Logic to create a credit note record and report to ZATCA
        // This will be implemented in TASK 4, but we can add a placeholder
    }

    public function getInvoiceStats($teamId, $from = null, $to = null)
    {
        $query = Invoice::where('team_id', $teamId);
        if ($from) $query->where('invoice_date', '>=', $from);
        if ($to) $query->where('invoice_date', '<=', $to);

        return [
            'total_count' => $query->count(),
            'total_amount' => $query->sum('grand_total'),
            'outstanding' => $query->whereIn('status', ['sent', 'partially_paid'])->sum('grand_total'),
            'zatca_pending' => $query->where('zatca_status', 'pending')->count(),
            'by_status' => $query->select('status', DB::raw('count(*) as count'))->groupBy('status')->pluck('count', 'status'),
        ];
    }
}
