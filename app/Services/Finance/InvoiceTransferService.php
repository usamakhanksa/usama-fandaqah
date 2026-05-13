<?php

namespace App\Services\Finance;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceTransfer;
use App\Models\InvoiceTransferItem;
use App\Models\TeamCounter;
use App\Models\CreditNote;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class InvoiceTransferService
{
    protected $creditNoteService;
    protected $invoiceService;

    public function __construct(CreditNoteService $creditNoteService, InvoiceService $invoiceService)
    {
        $this->creditNoteService = $creditNoteService;
        $this->invoiceService = $invoiceService;
    }

    /**
     * Create a transfer request.
     */
    public function createTransfer(array $data): InvoiceTransfer
    {
        return DB::transaction(function () use ($data) {
            $fromInvoice = Invoice::findOrFail($data['from_invoice_id']);
            $toInvoice = isset($data['to_invoice_id']) ? Invoice::findOrFail($data['to_invoice_id']) : null;

            $transfer = InvoiceTransfer::create([
                'team_id' => $fromInvoice->team_id,
                'from_invoice_id' => $fromInvoice->id,
                'to_invoice_id' => $toInvoice?->id,
                'from_guest_id' => $fromInvoice->guest_id,
                'to_guest_id' => $data['to_guest_id'] ?? $toInvoice?->guest_id,
                'from_company_id' => $fromInvoice->company_id,
                'to_company_id' => $data['to_company_id'] ?? $toInvoice?->company_id,
                'from_reservation_id' => $fromInvoice->reservation_id,
                'to_reservation_id' => $data['to_reservation_id'] ?? $toInvoice?->reservation_id,
                'transfer_number' => $this->generateTransferNumber($fromInvoice->team_id),
                'transfer_date' => now(),
                'amount' => 0, // Will be updated
                'vat_amount' => 0,
                'total_amount' => 0,
                'reason' => $data['reason'],
                'transfer_type' => $data['transfer_type'] ?? 'item_level',
                'status' => 'pending',
                'created_by' => $data['created_by'] ?? Auth::id(),
            ]);

            $totalAmount = 0;
            $vatAmount = 0;
            $amount = 0;

            foreach ($data['items'] as $itemData) {
                $fromItem = InvoiceItem::findOrFail($itemData['id']);
                $qty = $itemData['quantity'] ?? $fromItem->quantity;

                // Calculate portion of amounts being transferred
                $ratio = $qty / $fromItem->quantity;
                $itemAmount = $fromItem->sub_total * $ratio;
                $itemVat = $fromItem->vat_amount * $ratio;
                $itemTotal = $fromItem->total_amount * $ratio;

                InvoiceTransferItem::create([
                    'invoice_transfer_id' => $transfer->id,
                    'from_invoice_item_id' => $fromItem->id,
                    'amount' => $itemTotal,
                ]);

                $amount += $itemAmount;
                $vatAmount += $itemVat;
                $totalAmount += $itemTotal;
            }

            $transfer->update([
                'amount' => $amount,
                'vat_amount' => $vatAmount,
                'total_amount' => $totalAmount,
            ]);

            // Auto-approve if below threshold (e.g., 0 for now as per requirement)
            if ($this->shouldAutoApprove($transfer)) {
                $this->completeTransfer($transfer);
            }

            return $transfer;
        });
    }

    /**
     * Complete the transfer (logic for credit note + new charges).
     */
    public function completeTransfer(InvoiceTransfer $transfer): void
    {
        DB::transaction(function () use ($transfer) {
            if ($transfer->status === 'completed') return;

            $fromInvoice = $transfer->fromInvoice;
            
            // 1. Create Credit Note for Source Invoice
            $selectedItems = $transfer->items->map(function ($tItem) {
                $fromItem = $tItem->fromItem;
                return [
                    'id' => $fromItem->id,
                    'quantity' => ($tItem->amount / $fromItem->total_amount) * $fromItem->quantity,
                ];
            })->toArray();

            $creditNote = $this->creditNoteService->createPartialCreditNote($fromInvoice, $selectedItems, [
                'reason' => 'correction',
                'reason_description' => "Transferred to " . ($transfer->toInvoice?->invoice_number ?? 'New Invoice'),
                'created_by' => $transfer->created_by,
            ]);
            $this->creditNoteService->processCreditNote($creditNote);

            // 2. Create Target Invoice or update existing
            $toInvoice = $transfer->toInvoice;
            if (!$toInvoice) {
                $toInvoice = $this->invoiceService->createInvoice([
                    'team_id' => $transfer->team_id,
                    'reservation_id' => $transfer->to_reservation_id,
                    'guest_id' => $transfer->to_guest_id,
                    'company_id' => $transfer->to_company_id,
                    'invoice_date' => now(),
                    'status' => 'confirmed',
                ], []);
                $transfer->update(['to_invoice_id' => $toInvoice->id]);
            }

            // 3. Add charges to Target Invoice
            foreach ($transfer->items as $tItem) {
                $fromItem = $tItem->fromItem;
                $qty = ($tItem->amount / $fromItem->total_amount) * $fromItem->quantity;
                
                $newItem = InvoiceItem::create([
                    'invoice_id' => $toInvoice->id,
                    'team_id' => $toInvoice->team_id,
                    'product_name' => $fromItem->product_name . " (Transferred from {$fromInvoice->invoice_number})",
                    'product_name_ar' => $fromItem->product_name_ar,
                    'quantity' => $qty,
                    'unit_price' => $fromItem->unit_price,
                    'sub_total' => $fromItem->sub_total * ($qty / $fromItem->quantity),
                    'discount_amount' => $fromItem->discount_amount * ($qty / $fromItem->quantity),
                    'taxable_amount' => $fromItem->taxable_amount * ($qty / $fromItem->quantity),
                    'vat_amount' => $fromItem->vat_amount * ($qty / $fromItem->quantity),
                    'vat_percentage' => $fromItem->vat_percentage,
                    'total_amount' => $tItem->amount,
                    'item_type' => $fromItem->item_type,
                ]);

                $tItem->update(['to_invoice_item_id' => $newItem->id]);
            }

            $this->invoiceService->updateInvoiceTotals($toInvoice);

            $transfer->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        });
    }

    public function approveTransfer(InvoiceTransfer $transfer): void
    {
        $transfer->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        $this->completeTransfer($transfer);
    }

    public function rejectTransfer(InvoiceTransfer $transfer, string $reason): void
    {
        $transfer->update([
            'status' => 'rejected',
            'rejected_by' => Auth::id(),
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    protected function shouldAutoApprove(InvoiceTransfer $transfer): bool
    {
        // Threshold logic could go here
        return true; 
    }

    public function generateTransferNumber($teamId): string
    {
        $date = now();
        $prefix = 'TRF-' . $date->format('Ym') . '-';

        return DB::transaction(function () use ($teamId, $prefix) {
            $counter = TeamCounter::where('team_id', $teamId)
                ->where('type', 'invoice_transfer')
                ->lockForUpdate()
                ->first();

            if (!$counter) {
                $counter = TeamCounter::create([
                    'team_id' => $teamId,
                    'type' => 'invoice_transfer',
                    'value' => 1,
                ]);
            } else {
                $counter->increment('value');
            }

            return $prefix . str_pad($counter->value, 4, '0', STR_PAD_LEFT);
        });
    }
}
