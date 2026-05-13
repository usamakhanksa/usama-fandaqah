<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Invoice;
use App\Models\InvoiceTransfer;
use App\Models\Team;
use App\User;
use App\Services\Finance\InvoiceTransferService;
use App\Services\Finance\CreditNoteService;
use App\Services\Finance\InvoiceService;
use App\Services\Finance\ZatcaService;
use Illuminate\Support\Facades\DB;

class InvoiceTransferSeeder extends Seeder
{
    public function run()
    {
        $team = Team::first();
        if (!$team) return;

        $user = User::where('current_team_id', $team->id)->first() ?? User::first();
        if (!$user) return;

        $service = new InvoiceTransferService(
            new CreditNoteService(),
            new InvoiceService(new ZatcaService())
        );

        // Find some invoices with items
        $invoices = Invoice::with('items')->where('team_id', $team->id)
            ->whereIn('status', ['confirmed', 'paid'])
            ->take(10)
            ->get();

        if ($invoices->count() < 2) return;

        foreach ($invoices as $index => $invoice) {
            if ($index % 2 !== 0) continue; // Skip every other
            
            $targetInvoice = $invoices[$index + 1] ?? null;
            if (!$targetInvoice) continue;

            $itemsToTransfer = [];
            foreach ($invoice->items->take(2) as $item) {
                $itemsToTransfer[] = [
                    'id' => $item->id,
                    'quantity' => $item->quantity,
                ];
            }

            if (empty($itemsToTransfer)) continue;

            DB::transaction(function() use ($service, $invoice, $targetInvoice, $itemsToTransfer, $user) {
                $transfer = $service->createTransfer([
                    'from_invoice_id' => $invoice->id,
                    'to_invoice_id' => $targetInvoice->id,
                    'reason' => 'Guest requested charges to be moved to company folio',
                    'transfer_type' => 'item_level',
                    'items' => $itemsToTransfer,
                    'created_by' => $user->id,
                ]);

                // Some completed, some pending
                if ($invoice->id % 2 === 0) {
                    $service->completeTransfer($transfer);
                }
            });
        }
    }
}
