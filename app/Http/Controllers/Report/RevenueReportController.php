<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Services\Reports\RevenueCalculationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RevenueReportController extends Controller
{
    protected $revenueService;

    public function __construct(RevenueCalculationService $revenueService)
    {
        $this->revenueService = $revenueService;
    }

    public function index()
    {
        return Inertia::render('Reports/RevenueReport', [
            'startDate' => Carbon::today()->startOfMonth()->toDateString(),
            'endDate' => Carbon::today()->toDateString(),
        ]);
    }

    public function generate(Request $request)
    {
        $startDate = Carbon::parse($request->get('start_date', Carbon::today()->startOfMonth()));
        $endDate = Carbon::parse($request->get('end_date', Carbon::today()));
        $teamId = auth()->user()->team_id;

        $this->revenueService->setTeamId($teamId);

        $summary = $this->revenueService->getRevenueSummary($startDate, $endDate);
        $bySource = $this->revenueService->getRevenueBySource($startDate, $endDate);
        $bySegment = $this->revenueService->getRevenueByMarketSegment($startDate, $endDate);
        $trend = $this->revenueService->getDailyRevenueTrend($startDate, $endDate);

        return response()->json([
            'summary' => $summary,
            'by_source' => $bySource,
            'by_segment' => $bySegment,
            'trend' => $trend,
        ]);
    }
}
