<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PromissoryNote;
use App\Models\PromissoryCollection;
use App\Models\Team;
use App\Models\Company;
use App\Models\Guest;
use App\User;
use App\Services\Finance\PromissoryNoteService;
use App\Services\Finance\CreditNoteService;
use App\Services\Finance\InvoiceService;
use App\Services\Finance\ZatcaService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PromissorySeeder extends Seeder
{
    public function run()
    {
        $team = Team::first();
        if (!$team) return;

        $user = User::where('current_team_id', $team->id)->first() ?? User::first();
        if (!$user) return;

        // Clear existing for clean seed
        PromissoryNote::where('team_id', $team->id)->forceDelete();
        \App\Models\TeamCounter::where('team_id', $team->id)->whereIn('type', ['promissory_note', 'receipt', 'invoice_transfer'])->delete();
        \App\Models\Receipt::where('team_id', $team->id)->forceDelete();

        $service = new PromissoryNoteService();
        $companies = Company::where('team_id', $team->id)->take(10)->get();
        $guests = Guest::where('team_id', $team->id)->take(10)->get();

        if ($companies->isEmpty() && $guests->isEmpty()) return;

        // Create 25 promissory notes
        for ($i = 1; $i <= 25; $i++) {
            $isCompany = rand(0, 1);
            $beneficiary = $isCompany ? $companies->random() : $guests->random();
            
            $issueDate = Carbon::now()->subDays(rand(10, 120));
            $dueDate = (clone $issueDate)->addDays(rand(30, 90));
            $amount = rand(5000, 50000);

            $note = $service->createPromissoryNote([
                'team_id' => $team->id,
                'company_id' => $isCompany ? $beneficiary->id : null,
                'guest_id' => !$isCompany ? $beneficiary->id : null,
                'amount' => $amount,
                'issue_date' => $issueDate,
                'due_date' => $dueDate,
                'signatory_name' => 'Signatory ' . $i,
                'signatory_id_number' => 'ID-' . rand(1000000, 9999999),
                'status' => 'pending',
                'created_by' => $user->id,
            ]);

            // Set some as defaulted or renewed
            if ($i <= 3) {
                $note->update(['status' => 'defaulted']);
            } elseif ($i <= 5) {
                $note->update(['status' => 'renewed', 'due_date' => Carbon::now()->addDays(30)]);
            }
        }

        // Create 40 collections across them
        $notes = PromissoryNote::all();
        for ($j = 1; $j <= 40; $j++) {
            $note = $notes->random();
            if ($note->remaining_amount <= 0) continue;

            $maxCollect = $note->remaining_amount;
            $collectAmount = rand(100, min($maxCollect, 5000));
            
            $service->recordCollection($note, [
                'amount' => $collectAmount,
                'collection_date' => Carbon::now()->subDays(rand(0, 30)),
                'payment_method' => collect(['cash', 'card', 'bank_transfer', 'cheque'])->random(),
                'created_by' => $user->id,
            ]);
        }

        // Mark some as overdue
        $service->checkOverdue();
    }
}