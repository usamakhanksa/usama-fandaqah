<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Team;
use App\Models\TeamCounter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoiceService
{
    public function listInvoices($filters = [], $perPage = 15)
    {
        $query = Invoice::with(['guest', 'company', 'reservation', 'creator'])
            ->byTeam(auth()->user()->current_team_id);

        // Apply filters
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('guest', function ($guestQuery) use ($search) {
                      $guestQuery->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('company', function ($companyQuery) use ($search) {
                      $companyQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if (!empty($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (!empty($filters['zatca_status'])) {
            $query->where('zatca_status', $filters['zatca_status']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('invoice_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('invoice_date', '<=', $filters['date_to']);
        }

        if (!empty($filters['guest_id'])) {
            $query->byGuest($filters['guest_id']);
        }

        if (!empty($filters['company_id'])) {
            $query->byCompany($filters['company_id']);
        }

        if (!empty($filters['invoice_type'])) {
            $query->where('invoice_type', $filters['invoice_type']);
        }

        return $query->orderBy('invoice_date', 'desc')
                   ->orderBy('created_at', 'desc')
                   ->paginate($perPage);
    }

    public function getInvoice($id)
    {
        return Invoice::with(['items', 'guest', 'company', 'reservation', 'creator', 'updater'])
            ->byTeam(auth()->user()->current_team_id)
            ->findOrFail($id);
    }

    public function createInvoice(array $data): Invoice
    {
        return DB::transaction(function () use ($data) {
            // Generate invoice number
            $invoiceNumber = $this->generateInvoiceNumber($data['team_id']);

            // Create invoice
            $invoice = Invoice::create([
                'team_id' => $data['team_id'],
                'reservation_id' => $data['reservation_id'] ?? null,
                'guest_id' => $data['guest_id'] ?? null,
                'company_id' => $data['company_id'] ?? null,
                'folio_id' => $data['folio_id'] ?? null,
                'invoice_number' => $invoiceNumber,
                'invoice_type' => $data['invoice_type'] ?? 'standard',
                'invoice_type_code' => $this->getInvoiceTypeCode($data['invoice_type'] ?? 'standard'),
                'invoice_date' => $data['invoice_date'],
                'invoice_time' => $data['invoice_time'] ?? null,
                'due_date' => $data['due_date'] ?? null,
                'currency' => $data['currency'] ?? 'SAR',
                'tax_rate' => $data['tax_rate'] ?? 15.00,
                'notes' => $data['notes'] ?? null,
                'internal_notes' => $data['internal_notes'] ?? null,
                'terms' => $data['terms'] ?? null,
                'billing_name' => $data['billing_name'] ?? null,
                'billing_address' => $data['billing_address'] ?? null,
                'billing_city' => $data['billing_city'] ?? null,
                'billing_state' => $data['billing_state'] ?? null,
                'billing_postal_code' => $data['billing_postal_code'] ?? null,
                'billing_country' => $data['billing_country'] ?? 'SA',
                'billing_vat_number' => $data['billing_vat_number'] ?? null,
                'billing_cr_number' => $data['billing_cr_number'] ?? null,
                'billing_email' => $data['billing_email'] ?? null,
                'billing_phone' => $data['billing_phone'] ?? null,
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            // Create invoice items
            if (!empty($data['items'])) {
                foreach ($data['items'] as $index => $itemData) {
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'team_id' => $data['team_id'],
                        'description' => $itemData['description'],
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                        'discount_amount' => $itemData['discount_amount'] ?? 0,
                        'tax_rate' => $itemData['tax_rate'] ?? $data['tax_rate'] ?? 15.00,
                        'item_type' => $itemData['item_type'] ?? 'room_charge',
                        'reference_type' => $itemData['reference_type'] ?? null,
                        'reference_id' => $itemData['reference_id'] ?? null,
                        'sort_order' => $index,
                    ]);
                }
            }

            // Calculate totals
            $invoice->calculateTotals();
            $invoice->save();

            // Log activity
            activity()
                ->performedOn($invoice)
                ->causedBy(auth()->user())
                ->withProperties([
                    'invoice_number' => $invoice->invoice_number,
                    'total_amount' => $invoice->total_amount,
                    'action' => 'created',
                ])
                ->log('Invoice created');

            return $invoice->load('items');
        });
    }

    public function updateInvoice($id, array $data): Invoice
    {
        return DB::transaction(function () use ($id, $data) {
            $invoice = $this->getInvoice($id);

            if (!$invoice->can_be_edited) {
                throw new \Exception('Invoice cannot be edited. Only draft invoices can be modified.');
            }

            // Update invoice
            $invoice->update([
                'reservation_id' => $data['reservation_id'] ?? $invoice->reservation_id,
                'guest_id' => $data['guest_id'] ?? $invoice->guest_id,
                'company_id' => $data['company_id'] ?? $invoice->company_id,
                'folio_id' => $data['folio_id'] ?? $invoice->folio_id,
                'invoice_type' => $data['invoice_type'] ?? $invoice->invoice_type,
                'invoice_type_code' => $this->getInvoiceTypeCode($data['invoice_type'] ?? $invoice->invoice_type),
                'invoice_date' => $data['invoice_date'] ?? $invoice->invoice_date,
                'invoice_time' => $data['invoice_time'] ?? $invoice->invoice_time,
                'due_date' => $data['due_date'] ?? $invoice->due_date,
                'currency' => $data['currency'] ?? $invoice->currency,
                'tax_rate' => $data['tax_rate'] ?? $invoice->tax_rate,
                'notes' => $data['notes'] ?? $invoice->notes,
                'internal_notes' => $data['internal_notes'] ?? $invoice->internal_notes,
                'terms' => $data['terms'] ?? $invoice->terms,
                'billing_name' => $data['billing_name'] ?? $invoice->billing_name,
                'billing_address' => $data['billing_address'] ?? $invoice->billing_address,
                'billing_city' => $data['billing_city'] ?? $invoice->billing_city,
                'billing_state' => $data['billing_state'] ?? $invoice->billing_state,
                'billing_postal_code' => $data['billing_postal_code'] ?? $invoice->billing_postal_code,
                'billing_country' => $data['billing_country'] ?? $invoice->billing_country,
                'billing_vat_number' => $data['billing_vat_number'] ?? $invoice->billing_vat_number,
                'billing_cr_number' => $data['billing_cr_number'] ?? $invoice->billing_cr_number,
                'billing_email' => $data['billing_email'] ?? $invoice->billing_email,
                'billing_phone' => $data['billing_phone'] ?? $invoice->billing_phone,
                'updated_by' => auth()->id(),
            ]);

            // Update existing items and create new ones
            if (!empty($data['items'])) {
                // Delete existing items
                $invoice->items()->delete();

                // Create new items
                foreach ($data['items'] as $index => $itemData) {
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'team_id' => $invoice->team_id,
                        'description' => $itemData['description'],
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                        'discount_amount' => $itemData['discount_amount'] ?? 0,
                        'tax_rate' => $itemData['tax_rate'] ?? $data['tax_rate'] ?? $invoice->tax_rate,
                        'item_type' => $itemData['item_type'] ?? 'room_charge',
                        'reference_type' => $itemData['reference_type'] ?? null,
                        'reference_id' => $itemData['reference_id'] ?? null,
                        'sort_order' => $index,
                    ]);
                }
            }

            // Recalculate totals
            $invoice->calculateTotals();
            $invoice->save();

            // Log activity
            activity()
                ->performedOn($invoice)
                ->causedBy(auth()->user())
                ->withProperties([
                    'invoice_number' => $invoice->invoice_number,
                    'total_amount' => $invoice->total_amount,
                    'action' => 'updated',
                ])
                ->log('Invoice updated');

            return $invoice->load('items');
        });
    }

    public function deleteInvoice($id): bool
    {
        return DB::transaction(function () use ($id) {
            $invoice = $this->getInvoice($id);

            if (!$invoice->can_be_edited) {
                throw new \Exception('Invoice cannot be deleted. Only draft invoices can be deleted.');
            }

            // Log activity
            activity()
                ->performedOn($invoice)
                ->causedBy(auth()->user())
                ->withProperties([
                    'invoice_number' => $invoice->invoice_number,
                    'total_amount' => $invoice->total_amount,
                    'action' => 'deleted',
                ])
                ->log('Invoice deleted');

            return $invoice->delete();
        });
    }

    public function sendInvoice($id): Invoice
    {
        return DB::transaction(function () use ($id) {
            $invoice = $this->getInvoice($id);

            if ($invoice->status !== 'draft') {
                throw new \Exception('Only draft invoices can be sent.');
            }

            // Prepare ZATCA data
            $invoice->zatca_uuid = $invoice->generateZatcaUuid();
            $invoice->zatca_hash = $invoice->generateZatcaHash();
            $invoice->zatca_qr_code = $invoice->generateQrCode();
            $invoice->zatca_xml = $invoice->generateXml();

            // Update status
            $invoice->status = 'sent';
            $invoice->updated_by = auth()->id();
            $invoice->save();

            // Log activity
            activity()
                ->performedOn($invoice)
                ->causedBy(auth()->user())
                ->withProperties([
                    'invoice_number' => $invoice->invoice_number,
                    'total_amount' => $invoice->total_amount,
                    'action' => 'sent',
                ])
                ->log('Invoice sent');

            return $invoice;
        });
    }

    public function sendToZatca($id): Invoice
    {
        return DB::transaction(function () use ($id) {
            $invoice = $this->getInvoice($id);

            if (!$invoice->can_send_to_zatca) {
                throw new \Exception('Invoice cannot be sent to ZATCA.');
            }

            try {
                // Update ZATCA status to pending
                $invoice->update([
                    'zatca_status' => 'pending',
                    'zatca_sent_at' => now(),
                ]);

                // Call ZATCA API
                $zatcaService = new ZatcaService();
                $response = $zatcaService->reportInvoice($invoice);

                // Update based on response
                if ($response['success']) {
                    $invoice->update([
                        'zatca_status' => 'accepted',
                        'zatca_responded_at' => now(),
                        'zatca_clearance_number' => $response['clearance_number'] ?? null,
                        'is_zatca_reported' => true,
                    ]);
                } else {
                    $invoice->update([
                        'zatca_status' => 'rejected',
                        'zatca_responded_at' => now(),
                        'zatca_rejection_reason' => $response['message'] ?? 'Unknown error',
                    ]);
                }

                // Log activity
                activity()
                    ->performedOn($invoice)
                    ->causedBy(auth()->user())
                    ->withProperties([
                        'invoice_number' => $invoice->invoice_number,
                        'zatca_status' => $invoice->zatca_status,
                        'action' => 'zatca_submitted',
                    ])
                    ->log('Invoice submitted to ZATCA');

            } catch (\Exception $e) {
                Log::error('ZATCA submission failed', [
                    'invoice_id' => $invoice->id,
                    'error' => $e->getMessage(),
                ]);

                $invoice->update([
                    'zatca_status' => 'rejected',
                    'zatca_responded_at' => now(),
                    'zatca_rejection_reason' => 'API Error: ' . $e->getMessage(),
                ]);
            }

            return $invoice;
        });
    }

    public function voidInvoice($id, string $reason): Invoice
    {
        return DB::transaction(function () use ($id, $reason) {
            $invoice = $this->getInvoice($id);

            $invoice->void($reason, auth()->id());

            // If ZATCA reported, send cancellation
            if ($invoice->is_zatca_reported) {
                try {
                    $zatcaService = new ZatcaService();
                    $zatcaService->cancelInvoice($invoice);
                } catch (\Exception $e) {
                    Log::error('ZATCA cancellation failed', [
                        'invoice_id' => $invoice->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Log activity
            activity()
                ->performedOn($invoice)
                ->causedBy(auth()->user())
                ->withProperties([
                    'invoice_number' => $invoice->invoice_number,
                    'reason' => $reason,
                    'action' => 'voided',
                ])
                ->log('Invoice voided');

            return $invoice;
        });
    }

    public function applyPayment($id, float $amount): Invoice
    {
        return DB::transaction(function () use ($id, $amount) {
            $invoice = $this->getInvoice($id);

            $invoice->applyPayment($amount);

            // Log activity
            activity()
                ->performedOn($invoice)
                ->causedBy(auth()->user())
                ->withProperties([
                    'invoice_number' => $invoice->invoice_number,
                    'amount_applied' => $amount,
                    'new_amount_paid' => $invoice->amount_paid,
                    'action' => 'payment_applied',
                ])
                ->log('Payment applied to invoice');

            return $invoice;
        });
    }

    public function generatePdf($id): string
    {
        $invoice = $this->getInvoice($id);
        
        // This would integrate with a PDF generation library
        // For now, return placeholder
        return 'pdf_path_' . $invoice->invoice_number . '.pdf';
    }

    public function getDailySummary($date = null): array
    {
        $date = $date ?? now()->format('Y-m-d');
        $teamId = auth()->user()->current_team_id;

        $invoices = Invoice::byTeam($teamId)
            ->whereDate('invoice_date', $date)
            ->get();

        return [
            'date' => $date,
            'total_invoices' => $invoices->count(),
            'total_amount' => $invoices->sum('total_amount'),
            'total_tax' => $invoices->sum('tax_amount'),
            'by_status' => $invoices->groupBy('status')->map->count(),
            'by_zatca_status' => $invoices->groupBy('zatca_status')->map->count(),
        ];
    }

    public function exportInvoices($filters = []): string
    {
        $query = Invoice::byTeam(auth()->user()->current_team_id)
            ->with(['guest', 'company']);

        // Apply same filters as listInvoices
        if (!empty($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('invoice_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('invoice_date', '<=', $filters['date_to']);
        }

        $invoices = $query->orderBy('invoice_date', 'desc')->get();

        // Generate Excel export
        $filename = 'invoices_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        
        // This would integrate with Laravel Excel
        // For now, return placeholder
        return $filename;
    }

    public function generateInvoiceNumber($teamId): string
    {
        $date = now()->format('Ymd');
        $prefix = 'INV-' . $date . '-';
        
        // Get team counter
        $counter = TeamCounter::where('team_id', $teamId)
            ->where('counter_type', 'invoice')
            ->whereDate('date', now())
            ->first();

        if (!$counter) {
            $counter = TeamCounter::create([
                'team_id' => $teamId,
                'counter_type' => 'invoice',
                'date' => now(),
                'value' => 1,
            ]);
        } else {
            $counter->increment('value');
        }

        return $prefix . str_pad($counter->value, 4, '0', STR_PAD_LEFT);
    }

    private function getInvoiceTypeCode(string $invoiceType): string
    {
        return match($invoiceType) {
            'standard' => '388',
            'simplified' => '388',
            'debit_note' => '383',
            'credit_note' => '381',
            default => '388',
        };
    }
}
