<?php

namespace App\Services;

use App\Models\InvoiceTransfer;
use App\Models\Promissory;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;

class InvoiceTransferService
{
    public function transferToAR(array $data, int $userId)
    {
        return DB::transaction(function () use ($data, $userId) {
            $reservation = Reservation::findOrFail($data['reservation_id']);
            
            // Validate checkout status or readiness
            if ($reservation->stay_type !== 'checkout' && $reservation->stay_type !== 'checkin') {
                 throw new \Exception('Reservation must be checked in or checked out to transfer invoice.');
            }

            // 1. Prevent duplicate transfer for same invoice
            $exists = InvoiceTransfer::where('reservation_id', $data['reservation_id'])
                ->where('invoice_id', $data['invoice_id'] ?? null)
                ->exists();

            if ($exists && !($data['is_correction'] ?? false)) {
                throw new \Exception('This invoice has already been transferred to AR.');
            }

            // 2. Link or create Promissory note
            $promissory = Promissory::create([
                'team_id' => $reservation->team_id ?? 1,
                'reservation_id' => $reservation->id,
                'company_id' => $data['company_id'],
                'total_amount' => $data['amount'],
                'status' => 'active',
                'due_date' => now()->addDays(30), // Default 30 days credit
            ]);

            // 3. Create Transfer record
            return InvoiceTransfer::create([
                'team_id' => $reservation->team_id ?? 1,
                'reservation_id' => $data['reservation_id'],
                'invoice_id' => $data['invoice_id'] ?? null,
                'company_id' => $data['company_id'],
                'promissory_id' => $promissory->id,
                'amount' => $data['amount'],
                'transferred_by' => $userId,
                'transferred_at' => now(),
                'notes' => $data['notes'] ?? null,
            ]);

            // 3. Update reservation or transactions status if needed
            // e.g. mark folio as "transferred to AR"
        });
    }

    public function getTransfers(array $filters)
    {
        return InvoiceTransfer::with(['reservation', 'company', 'transferredBy', 'promissory'])
            ->filter($filters)
            ->paginate($filters['per_page'] ?? 20);
    }
}
