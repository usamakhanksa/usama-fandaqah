<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $teamId = $user->team_id;
        $team = Team::find($teamId);

        if (!$team) {
            return Inertia::render('Reports/Dashboard', ['stats' => []]);
        }

        $occupancyData = $this->dashboardService->getOccupancyData($team);
        $revenueData = $this->dashboardService->getRevenueData($team);

        $stats = [
            'occupancy' => $occupancyData['occupancy_rate'],
            'revenue' => $revenueData['today'],
            'mtd_revenue' => $revenueData['this_month'],
            'adr' => $occupancyData['occupied'] > 0 ? round($revenueData['today'] / $occupancyData['occupied'], 2) : 0,
        ];

        return Inertia::render('Reports/Dashboard', [
            'stats' => $stats
        ]);
    }
}
