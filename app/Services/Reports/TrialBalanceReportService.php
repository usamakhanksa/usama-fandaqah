<?php

namespace App\Services\Reports;

use Carbon\Carbon;
use App\Services\Finance\TrialBalanceService;

class TrialBalanceReportService extends ReportService
{
    protected $trialBalanceService;

    public function __construct(TrialBalanceService $trialBalanceService)
    {
        $this->trialBalanceService = $trialBalanceService;
    }

    /**
     * Get trial balance data for given date
     */
    public function getTrialBalance(Carbon $asOfDate)
    {
        $teamId = $this->teamId;
        return $this->trialBalanceService->generate($teamId, $asOfDate);
    }

    /**
     * Get trial balance with account categories organized
     */
    public function getOrganizedTrialBalance(Carbon $asOfDate)
    {
        $teamId = $this->teamId;
        $accounts = $this->trialBalanceService->generate($teamId, $asOfDate);

        // Group by account type (simplified)
        $assets = collect();
        $liabilities = collect();
        $equity = collect();
        $revenue = collect();
        $expenses = collect();
        $uncategorized = collect();

        foreach ($accounts as $account) {
            $accountName = strtolower($account['account_name']);
            
            if (in_array($accountName, ['cash', 'bank', 'receivables', 'accounts receivable', 'inventory', 'fixed assets', 'prepaid', 'deposit'])) {
                $assets->push($account);
            } elseif (in_array($accountName, ['payables', 'accounts payable', 'accrued', 'loan', 'credit card'])) {
                $liabilities->push($account);
            } elseif (in_array($accountName, ['equity', 'retained earnings', 'capital'])) {
                $equity->push($account);
            } elseif (in_array($accountName, ['sales', 'revenue', 'income', 'room revenue', 'service revenue', 'food revenue'])) {
                $revenue->push($account);
            } elseif (in_array($accountName, ['expenses', 'cost of goods sold', 'salaries', 'rent', 'utilities', 'supplies', 'maintenance', 'advertising'])) {
                $expenses->push($account);
            } else {
                $uncategorized->push($account);
            }
        }

        return [
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'revenue' => $revenue,
            'expenses' => $expenses,
            'uncategorized' => $uncategorized,
        ];
    }

    /**
     * Export trial balance to CSV/PDF
     */
    public function export($asOfDate, $format)
    {
        $teamId = $this->teamId;
        return $this->trialBalanceService->export($teamId, $asOfDate, $format);
    }
}
