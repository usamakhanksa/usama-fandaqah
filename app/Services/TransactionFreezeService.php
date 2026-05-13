<?php

namespace App\Services;

use App\Team;
use Illuminate\Support\Facades\DB;

class TransactionFreezeService
{
    public function freeze(Team $team)
    {
        $businessDate = $team->business_date;

        // 1. Update transactions
        $txCount = DB::table('transactions')
            ->where('team_id', $team->id)
            ->where('is_freezed', false)
            ->whereDate('created_at', '<=', $businessDate)
            ->update(['is_freezed' => true]);

        // 2. Link to business_date
        $unlinkedTx = DB::table('transactions')
            ->where('team_id', $team->id)
            ->whereDate('created_at', '<=', $businessDate)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('business_date_transactions')
                      ->whereRaw('business_date_transactions.transaction_id = transactions.id');
            })
            ->get(['id']);

        foreach ($unlinkedTx as $tx) {
            DB::table('business_date_transactions')->insert([
                'team_id' => $team->id,
                'transaction_id' => $tx->id,
                'business_date' => $businessDate,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // 3. Freeze service logs
        DB::table('service_logs')
            ->where('team_id', $team->id)
            ->where('is_freezed', false)
            ->whereDate('created_at', '<=', $businessDate)
            ->update([
                'is_freezed' => true,
                'business_date' => $businessDate
            ]);

        return $txCount;
    }
}
