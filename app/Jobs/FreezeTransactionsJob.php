<?php

namespace App\Jobs;

use App\Transaction;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FreezeTransactionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $teamId;
    protected $freezeDate;

    /**
     * Create a new job instance.
     */
    public function __construct($teamId, $freezeDate)
    {
        $this->teamId = $teamId;
        $this->freezeDate = $freezeDate;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        // Get the day before the freeze date, keeping the same time
        //    $dayBeforeFreeze = $freezeDate->copy()->subDay();
        $filters = [
            // 'by_date_from' => "2025-01-20 12:00",
            'by_date_to' => $this->freezeDate->format('Y-m-d H:i'),
        ];

        $transactions = DB::table('transactions as tr')
            ->select('tr.id')
            ->where('tr.team_id', $this->teamId)
            ->where('tr.amount', '!=', 0)
            ->where('tr.is_freezed', 0)
            ->whereNull('tr.deleted_at')
            ->when(isset($filters['by_date_to']), function ($query) use ($filters) {
                $to = Carbon::parse($filters['by_date_to'])->format('Y-m-d H:i');
                $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(tr.meta, '$.date')) <= ?", [$to]);
            })
            ->orderByDesc('tr.number')
            ->pluck('tr.id');

        if ($transactions->count() > 0) {
            Transaction::whereIn('id', $transactions)->update(['is_freezed' => 1]);


            Log::info("Team {$this->teamId} transactions have been frozen from the exact time the day before the freeze date.");
        }else{

            Log::info("No transactions to freeze for team {$this->teamId}.");
        }
            $freezeHour = $this->freezeDate->hour;
            $newDate = Carbon::now()->setHour($freezeHour)->setMinute(0)->setSecond(0);
            $newFreezeDate =$newDate;

            // Update the business_day_freeze_date in the database
            DB::table('settings')
                ->where('team_id', $this->teamId)
                ->where('key', 'business_day_freeze_date')
                ->update(['value' => $newFreezeDate->toDateTimeString()]);

            Log::info("Team {$this->teamId} business_day_freeze_date has been updated to {$newFreezeDate->toDateTimeString()}.");
    }
}
