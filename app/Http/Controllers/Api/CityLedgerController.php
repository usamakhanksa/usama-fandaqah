<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ARAgingService;
use Illuminate\Http\Request;

class CityLedgerController extends Controller
{
    protected $agingService;

    public function __construct(ARAgingService $agingService)
    {
        $this->agingService = $agingService;
    }

    public function dashboard(Request $request)
    {
        $teamId = auth()->user()->current_team_id;
        $stats = $this->agingService->getDashboardStats($teamId);
        return response()->json(['data' => $stats]);
    }

    public function agingReport(Request $request)
    {
        $teamId = auth()->user()->current_team_id;
        $filters = $request->only(['company_group_id', 'company_id']);
        $report = $this->agingService->getAgingReport($teamId, $filters);
        return response()->json(['data' => $report]);
    }

    public function export(Request $request)
    {
        // Simple JSON export for demo
        $teamId = auth()->user()->current_team_id;
        $report = $this->agingService->getAgingReport($teamId);
        return response()->json(['data' => $report, 'meta' => ['export_date' => now()]]);
    }
}
