<?php

namespace App\Services\Finance;

use App\Models\CreditNote;
use App\Models\CreditNoteItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\TeamCounter;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CreditNoteService
{
    /**
     * Create a credit note from an invoice.
     */
    public function createFromInvoice(Invoice $invoice, array $overrides = []): CreditNote
    {
        return DB::transaction(function () use ($invoice, $overrides) {
            $creditNote = CreditNote::create(array_merge([
                'team_id' => $invoice->team_id,
                'invoice_id' => $invoice->id,
                'reservation_id' => $invoice->reservation_id,
                'guest_id' => $invoice->guest_id,
                'company_id' => $invoice->company_id,
                'credit_note_number' => $this->generateCreditNoteNumber($invoice->team_id),
                'zatca_uuid' => \Illuminate\Support\Str::uuid(),
                'credit_note_date' => now(),
                'reason' => 'cancellation',
                'sub_total' => $invoice->sub_total,
                'discount_amount' => $invoice->discount_amount,
                'taxable_amount' => $invoice->taxable_amount,
                'vat_amount' => $invoice->vat_amount,
                'vat_percentage' => $invoice->vat_percentage,
                'total_amount' => $invoice->grand_total,
                'currency' => $invoice->currency,
                'status' => 'draft',
                'created_by' => $overrides['created_by'] ?? Auth::id(),
            ], $overrides));

            foreach ($invoice->items as $item) {
                CreditNoteItem::create([
                    'credit_note_id' => $creditNote->id,
                    'original_item_id' => $item->id,
                    'team_id' => $creditNote->team_id,
                    'product_name' => $item->product_name,
                    'product_name_ar' => $item->product_name_ar,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'sub_total' => $item->sub_total,
                    'discount_amount' => $item->discount_amount,
                    'taxable_amount' => $item->taxable_amount,
                    'vat_amount' => $item->vat_amount,
                    'vat_percentage' => $item->vat_percentage,
                    'total_amount' => $item->total_amount,
                    'item_type' => $item->item_type,
                ]);
            }

            return $creditNote;
        });
    }

    /**
     * Create a partial credit note for selected items.
     */
    public function createPartialCreditNote(Invoice $invoice, array $selectedItems, array $overrides = []): CreditNote
    {
        return DB::transaction(function () use ($invoice, $selectedItems, $overrides) {
            $creditNote = CreditNote::create(array_merge([
                'team_id' => $invoice->team_id,
                'invoice_id' => $invoice->id,
                'reservation_id' => $invoice->reservation_id,
                'guest_id' => $invoice->guest_id,
                'company_id' => $invoice->company_id,
                'credit_note_number' => $this->generateCreditNoteNumber($invoice->team_id),
                'zatca_uuid' => \Illuminate\Support\Str::uuid(),
                'credit_note_date' => now(),
                'reason' => 'correction',
                'sub_total' => 0,
                'discount_amount' => 0,
                'taxable_amount' => 0,
                'vat_amount' => 0,
                'vat_percentage' => 15.00,
                'total_amount' => 0,
                'currency' => $invoice->currency,
                'status' => 'draft',
                'created_by' => $overrides['created_by'] ?? Auth::id(),
            ], $overrides));

            $subTotal = 0;
            $discountAmount = 0;
            $taxableAmount = 0;
            $vatAmount = 0;
            $totalAmount = 0;

            foreach ($selectedItems as $selection) {
                $item = InvoiceItem::find($selection['id']);
                if ($item && $item->invoice_id === $invoice->id) {
                    $qty = $selection['quantity'] ?? $item->quantity;
                    
                    // Recalculate amounts based on qty
                    $itemSubTotal = ($item->unit_price * $qty);
                    $itemDiscount = ($item->discount_amount / $item->quantity) * $qty;
                    $itemTaxable = $itemSubTotal - $itemDiscount;
                    $itemVat = ($itemTaxable * ($item->vat_percentage / 100));
                    $itemTotal = $itemTaxable + $itemVat;

                    CreditNoteItem::create([
                        'credit_note_id' => $creditNote->id,
                        'original_item_id' => $item->id,
                        'team_id' => $creditNote->team_id,
                        'product_name' => $item->product_name,
                        'product_name_ar' => $item->product_name_ar,
                        'description' => $item->description,
                        'quantity' => $qty,
                        'unit_price' => $item->unit_price,
                        'sub_total' => $itemSubTotal,
                        'discount_amount' => $itemDiscount,
                        'taxable_amount' => $itemTaxable,
                        'vat_amount' => $itemVat,
                        'vat_percentage' => $item->vat_percentage,
                        'total_amount' => $itemTotal,
                        'item_type' => $item->item_type,
                    ]);

                    $subTotal += $itemSubTotal;
                    $discountAmount += $itemDiscount;
                    $taxableAmount += $itemTaxable;
                    $vatAmount += $itemVat;
                    $totalAmount += $itemTotal;
                }
            }

            $creditNote->update([
                'sub_total' => $subTotal,
                'discount_amount' => $discountAmount,
                'taxable_amount' => $taxableAmount,
                'vat_amount' => $vatAmount,
                'total_amount' => $totalAmount,
            ]);

            return $creditNote;
        });
    }

    /**
     * Process credit note and create reversal transaction.
     */
    public function processCreditNote(CreditNote $note): CreditNote
    {
        return DB::transaction(function () use ($note) {
            if ($note->status !== 'draft') {
                return $note;
            }

            // Create reversal transaction
            $transaction = Transaction::create([
                'team_id' => $note->team_id,
                'payable_type' => CreditNote::class,
                'payable_id' => $note->id,
                'type' => 'withdraw', // Reversing revenue
                'amount' => -$note->total_amount,
                'amount_without_tax' => -$note->taxable_amount,
                'tax_amount' => -$note->vat_amount,
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'number' => "CN-REV-{$note->credit_note_number}",
                'description' => "Credit Note Reversal for {$note->invoice->invoice_number}: {$note->reason_description}",
                'confirmed' => true,
                'business_date' => now()->toDateString(),
                'meta' => [
                    'category' => 'credit_note',
                    'reason' => $note->reason,
                    'original_invoice' => $note->invoice->invoice_number,
                    'currency' => $note->currency,
                ]
            ]);

            $note->update([
                'status' => 'confirmed',
                'transaction_id' => $transaction->id,
            ]);

            return $note;
        });
    }

    /**
     * Submit to ZATCA.
     */
    public function submitToZatca(CreditNote $note)
    {
        $zatcaService = new ZatcaService();
        // ZATCA reporting for credit notes is similar to invoices but with different type
        return $zatcaService->reportInvoice($note); 
    }

    /**
     * Thread-safe credit note number generation.
     */
    public function generateCreditNoteNumber($teamId): string
    {
        $date = now();
        $prefix = 'CN-' . $date->format('Ym') . '-';

        return DB::transaction(function () use ($teamId, $prefix) {
            $counter = TeamCounter::where('team_id', $teamId)
                ->where('type', 'credit_note')
                ->lockForUpdate()
                ->first();

            if (!$counter) {
                $counter = TeamCounter::create([
                    'team_id' => $teamId,
                    'type' => 'credit_note',
                    'value' => 1,
                ]);
            } else {
                $counter->increment('value');
            }

            return $prefix . str_pad($counter->value, 4, '0', STR_PAD_LEFT);
        });
    }
}
