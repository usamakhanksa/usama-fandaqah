<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Jobs\FreezeTransactionsJob;

class CheckBusinessDayFreeze extends Command
{
    protected $signature = 'check:business-day-freeze';
    protected $description = 'Check business_day_freeze_date and update transactions is_freezed field';

    public function handle()
    {
        // Get all team_ids where "enable_business_day_freeze" is 1
        $teams = DB::table('settings')
            ->where('key', 'enable_business_day_freeze')
            ->where('value', 1)
            ->pluck('team_id');  // Get an array of team_ids

        foreach ($teams as $teamId) {
            // Get the business_day_freeze_date for each team
            $freezeDate = DB::table('settings')
                ->where('team_id', $teamId)
                ->where('key', 'business_day_freeze_date')
                ->value('value');

            if ($freezeDate) {
                // Parse the freeze date (includes date and time)
                $freezeDate = Carbon::parse($freezeDate);
                // Compare the freeze date with the current timestamp (including hour)
                if (now()->format('Y-m-d H:i') >= $freezeDate->format('Y-m-d H:i')  ) {

                    FreezeTransactionsJob::dispatch($teamId, $freezeDate);
                } else {
                    $this->info("Current time does not match freeze time for team {$teamId}.");
                }
            } else {
                $this->info("Team {$teamId} does not have a business_day_freeze_date.");
            }
        }
    }
}
