<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Services\Reports\TrialBalanceReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TrialBalanceReportController extends Controller
{
    protected $trialBalanceService;

    public function __construct(TrialBalanceReportService $trialBalanceService)
    {
        $this->trialBalanceService = $trialBalanceService;
    }

    public function index()
    {
        return Inertia::render('Reports/TrialBalanceReport', [
            'asOfDate' => Carbon::today()->toDateString(),
        ]);
    }

    public function generate(Request $request)
    {
        $asOfDate = Carbon::parse($request->get('as_of_date', Carbon::today()));
        $teamId = auth()->user()->team_id;

        $this->trialBalanceService->setTeamId($teamId);

        $trialBalance = $this->trialBalanceService->getTrialBalance($asOfDate);
        $organized = $this->trialBalanceService->getOrganizedTrialBalance($asOfDate);

        // Calculate totals
        $totalDebits = $trialBalance->sum('debit');
        $totalCredits = $trialBalance->sum('credit');

        return response()->json([
            'trial_balance' => $trialBalance,
            'organized' => $organized,
            'totals' => [
                'debits' => $totalDebits,
                'credits' => $totalCredits,
                'balanced' => abs($totalDebits - $totalCredits) < 0.01,
            ],
        ]);
    }

    public function export(Request $request)
    {
        $asOfDate = Carbon::parse($request->get('as_of_date', Carbon::today()));
        $teamId = auth()->user()->team_id;

        $this->trialBalanceService->setTeamId($teamId);
        $trialBalance = $this->trialBalanceService->getTrialBalance($asOfDate);

        $filename = "trial_balance_report_{$asOfDate->toDateString()}.csv";
        $handle = fopen('php://output', 'w');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        fputcsv($handle, ['Trial Balance Report']);
        fputcsv($handle, ['As of Date', $asOfDate->toDateString()]);
        fputcsv($handle, []);
        fputcsv($handle, ['Account', 'Debit', 'Credit']);
        foreach ($trialBalance as $account) {
            fputcsv($handle, [
                $account['account_name'],
                $account['debit'] ? number_format($account['debit'], 2) : '0.00',
                $account['credit'] ? number_format($account['credit'], 2) : '0.00',
            ]);
        }
        fclose($handle);
        exit;
    }
}
