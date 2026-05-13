<?php

namespace App\Services\Finance;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class TrialBalanceService
{
    public function generate($teamId, $asOfDate)
    {
        return Transaction::where('team_id', $teamId)
            ->whereDate('transaction_date', '<=', $asOfDate)
            ->select(
                'account_name',
                DB::raw('SUM(CASE WHEN type = "debit" THEN amount ELSE 0 END) as total_debit'),
                DB::raw('SUM(CASE WHEN type = "credit" THEN amount ELSE 0 END) as total_credit')
            )
            ->groupBy('account_name')
            ->get()
            ->map(function ($item) {
                $balance = $item->total_debit - $item->total_credit;
                return [
                    'account_name' => $item->account_name,
                    'debit' => $balance > 0 ? $balance : 0,
                    'credit' => $balance < 0 ? abs($balance) : 0,
                ];
            });
    }

    public function getByAccount($teamId, $asOfDate)
    {
        return $this->generate($teamId, $asOfDate);
    }

    public function export($teamId, $asOfDate, $format)
    {
        // Placeholder for export logic (Excel/PDF)
        return ['message' => 'Exporting trial balance...'];
    }
}
