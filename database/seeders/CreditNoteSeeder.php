<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CreditNote;
use App\Models\CreditNoteItem;
use App\Models\Invoice;
use App\Models\Team;
use App\User;
use App\Models\Transaction;
use App\Services\Finance\CreditNoteService;
use Illuminate\Support\Facades\DB;

class CreditNoteSeeder extends Seeder
{
    public function run()
    {
        $service = new CreditNoteService();
        $team = Team::first();
        if (!$team) return;

        $user = User::where('current_team_id', $team->id)->first() ?? User::first();
        if (!$user) return;

        // Ensure we have some invoices to credit
        $invoices = Invoice::with('items')->where('team_id', $team->id)
            ->whereIn('status', ['confirmed', 'paid'])
            ->take(15)
            ->get();

        if ($invoices->isEmpty()) {
            return;
        }

        $reasons = ['cancellation', 'correction', 'discount', 'partial_refund', 'other'];

        foreach ($invoices as $index => $invoice) {
            DB::transaction(function() use ($service, $invoice, $user, $reasons, $index) {
                $reason = $reasons[array_rand($reasons)];
                
                if ($index % 3 === 0) {
                    // Partial
                    $itemsToCredit = [];
                    foreach ($invoice->items->take(1) as $item) {
                        $itemsToCredit[] = [
                            'id' => $item->id,
                            'quantity' => max(0.1, $item->quantity / 2),
                        ];
                    }
                    $creditNote = $service->createPartialCreditNote($invoice, $itemsToCredit, [
                        'reason' => $reason,
                        'reason_description' => 'Partial refund for service',
                        'created_by' => $user->id,
                    ]);
                } else {
                    // Full
                    $creditNote = $service->createFromInvoice($invoice, [
                        'reason' => $reason,
                        'reason_description' => 'Full cancellation',
                        'created_by' => $user->id,
                    ]);
                }

                $service->processCreditNote($creditNote);

                if ($index < 10) {
                    $creditNote->update([
                        'is_zatca_reported' => true,
                        'zatca_status' => 'reported',
                        'zatca_submitted_at' => now(),
                        'zatca_uuid' => \Illuminate\Support\Str::uuid(),
                        'zatca_xml' => '<xml>dummy</xml>',
                        'zatca_qr_code' => 'dummy_qr',
                    ]);
                }
            });
        }
    }
}
