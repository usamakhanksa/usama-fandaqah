<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Services\Reports\OccupancyCalculationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OccupancyReportController extends Controller
{
    protected $occupancyService;

    public function __construct(OccupancyCalculationService $occupancyService)
    {
        $this->occupancyService = $occupancyService;
    }

    public function index()
    {
        return Inertia::render('Reports/OccupancyReport', [
            'startDate' => Carbon::today()->startOfMonth()->toDateString(),
            'endDate' => Carbon::today()->toDateString(),
        ]);
    }

    public function generate(Request $request)
    {
        $startDate = Carbon::parse($request->get('start_date', Carbon::today()->startOfMonth()));
        $endDate = Carbon::parse($request->get('end_date', Carbon::today()));
        $teamId = auth()->user()->team_id;

        $this->occupancyService->setTeamId($teamId);

        $dailyStats = $this->occupancyService->getRangeStats($startDate, $endDate);
        $byRoomType = $this->occupancyService->getOccupancyByRoomType($endDate);
        $byFloor = $this->occupancyService->getOccupancyByFloor($endDate);

        return response()->json([
            'daily_stats' => $dailyStats,
            'by_room_type' => $byRoomType,
            'by_floor' => $byFloor,
        ]);
    }
}
