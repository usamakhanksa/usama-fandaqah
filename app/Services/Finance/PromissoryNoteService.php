<?php

namespace App\Services\Finance;

use App\Models\PromissoryNote;
use App\Models\PromissoryCollection;
use App\Models\PromissoryCollectionLog;
use App\Models\TeamCounter;
use App\Models\Receipt;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PromissoryNoteService
{
    /**
     * Create a new promissory note.
     */
    public function createPromissoryNote(array $data): PromissoryNote
    {
        return DB::transaction(function () use ($data) {
            $data['team_id'] = $data['team_id'] ?? Auth::user()->current_team_id;
            $data['promissory_number'] = $this->generatePromissoryNumber($data['team_id']);
            $data['remaining_amount'] = $data['amount'];
            $data['created_by'] = $data['created_by'] ?? Auth::id();
            
            $note = PromissoryNote::create($data);

            $this->logAction($note, 'created', 'Promissory note created');

            return $note;
        });
    }

    /**
     * Record a collection against a promissory note.
     */
    public function recordCollection(PromissoryNote $note, array $data): PromissoryCollection
    {
        return DB::transaction(function () use ($note, $data) {
            // 1. Create Receipt (Placeholder for actual receipt logic)
            $receipt = Receipt::create([
                'team_id' => $note->team_id,
                'guest_id' => $note->guest_id,
                'company_id' => $note->company_id,
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'],
                'receipt_date' => $data['collection_date'],
                'status' => 'confirmed',
                'description' => "Collection for Promissory Note {$note->promissory_number}",
                'created_by' => $data['created_by'] ?? Auth::id(),
            ]);

            // 2. Create Transaction
            $transaction = Transaction::create([
                'team_id' => $note->team_id,
                'payable_type' => PromissoryNote::class,
                'payable_id' => $note->id,
                'type' => 'deposit',
                'amount' => $data['amount'],
                'number' => "COL-{$note->promissory_number}",
                'description' => "Collection payment for PN {$note->promissory_number}",
                'confirmed' => true,
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'business_date' => now()->toDateString(),
                'meta' => ['payment_method' => $data['payment_method']]
            ]);

            // 3. Create Collection record
            $collection = PromissoryCollection::create([
                'promissory_note_id' => $note->id,
                'team_id' => $note->team_id,
                'collection_date' => $data['collection_date'],
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'],
                'reference_number' => $data['reference_number'] ?? null,
                'receipt_id' => $receipt->id,
                'transaction_id' => $transaction->id,
                'notes' => $data['notes'] ?? null,
                'collected_by' => Auth::id() ?? $data['created_by'],
            ]);

            $this->logAction($note, 'collection_recorded', "Recorded collection of {$data['amount']} via {$data['payment_method']}");

            return $collection;
        });
    }

    /**
     * Reverse a collection.
     */
    public function reverseCollection(PromissoryCollection $collection): void
    {
        DB::transaction(function () use ($collection) {
            if ($collection->status === 'reversed') return;

            // 1. Reverse Transaction (withdraw)
            Transaction::create([
                'team_id' => $collection->team_id,
                'payable_type' => PromissoryNote::class,
                'payable_id' => $collection->promissory_note_id,
                'type' => 'withdraw',
                'amount' => -$collection->amount,
                'number' => "REV-COL-{$collection->id}",
                'description' => "Reversal of collection ID {$collection->id}",
                'confirmed' => true,
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'business_date' => now()->toDateString(),
                'correction_of_transaction_id' => $collection->transaction_id,
            ]);

            $collection->update([
                'status' => 'reversed',
                'reversed_at' => now(),
                'reversed_by' => Auth::id(),
            ]);

            $this->logAction($collection->promissoryNote, 'reversed', "Reversed collection of {$collection->amount}");
        });
    }

    /**
     * Check and mark overdue notes.
     */
    public function checkOverdue(): int
    {
        $notes = PromissoryNote::whereIn('status', ['pending', 'partially_collected'])
            ->where('due_date', '<', Carbon::now())
            ->where('is_overdue', false)
            ->get();

        foreach ($notes as $note) {
            $note->updateOverdueStatus();
            $this->logAction($note, 'overdue_marked', "Marked as overdue. Days: {$note->overdue_days}");
        }

        return $notes->count();
    }

    /**
     * Renew/Extend a note.
     */
    public function renewNote(PromissoryNote $note, string $newDueDate): void
    {
        $oldDueDate = $note->due_date;
        $note->update([
            'due_date' => $newDueDate,
            'status' => 'renewed',
            'is_overdue' => false,
            'overdue_days' => 0,
        ]);

        $this->logAction($note, 'renewed', "Renewed note. New due date: {$newDueDate}", [
            'old_due_date' => $oldDueDate->toDateString()
        ], [
            'new_due_date' => $newDueDate
        ]);
    }

    /**
     * Generate aging report data.
     */
    public function getAgingReport($teamId): array
    {
        $now = Carbon::now();
        
        return [
            'current' => PromissoryNote::where('team_id', $teamId)->where('is_overdue', false)->whereIn('status', ['pending', 'partially_collected'])->sum('remaining_amount'),
            '1_30' => PromissoryNote::where('team_id', $teamId)->whereBetween('overdue_days', [1, 30])->sum('remaining_amount'),
            '31_60' => PromissoryNote::where('team_id', $teamId)->whereBetween('overdue_days', [31, 60])->sum('remaining_amount'),
            '61_90' => PromissoryNote::where('team_id', $teamId)->whereBetween('overdue_days', [61, 90])->sum('remaining_amount'),
            'over_90' => PromissoryNote::where('team_id', $teamId)->where('overdue_days', '>', 90)->sum('remaining_amount'),
        ];
    }

    public function generatePromissoryNumber($teamId): string
    {
        $date = now();
        $prefix = 'PN-' . $date->format('Ym') . '-';

        return DB::transaction(function () use ($teamId, $prefix) {
            $counter = TeamCounter::where('team_id', $teamId)
                ->where('type', 'promissory_note')
                ->lockForUpdate()
                ->first();

            if (!$counter) {
                $counter = TeamCounter::create([
                    'team_id' => $teamId,
                    'type' => 'promissory_note',
                    'value' => 1,
                ]);
            } else {
                $counter->increment('value');
            }

            return $prefix . str_pad($counter->value, 4, '0', STR_PAD_LEFT);
        });
    }

    protected function logAction(PromissoryNote $note, string $action, string $description, ?array $old = null, ?array $new = null): void
    {
        PromissoryCollectionLog::create([
            'promissory_note_id' => $note->id,
            'action' => $action,
            'description' => $description,
            'old_values' => $old,
            'new_values' => $new,
            'performed_by' => Auth::id() ?? $note->created_by,
        ]);
    }
}
