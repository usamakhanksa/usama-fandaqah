<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Services\Finance\TrialBalanceService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TrialBalanceController extends Controller
{
    protected $trialBalanceService;

    public function __construct(TrialBalanceService $trialBalanceService)
    {
        $this->trialBalanceService = $trialBalanceService;
    }

    public function index(Request $request)
    {
        $asOfDate = $request->input('date', now()->toDateString());
        $teamId = auth()->user()->current_team_id;

        $trialBalance = $this->trialBalanceService->generate($teamId, $asOfDate);

        return Inertia::render('Finance/Reports/TrialBalance', [
            'trialBalance' => $trialBalance,
            'asOfDate' => $asOfDate,
        ]);
    }

    public function export(Request $request)
    {
        $asOfDate = $request->input('date', now()->toDateString());
        $format = $request->input('format', 'pdf');
        $teamId = auth()->user()->current_team_id;

        return $this->trialBalanceService->export($teamId, $asOfDate, $format);
    }
}
