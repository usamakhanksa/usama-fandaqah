<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Services\Reports\TurnawayReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TurnawayReportController extends Controller
{
    protected $turnawayService;

    public function __construct(TurnawayReportService $turnawayService)
    {
        $this->turnawayService = $turnawayService;
    }

    public function index()
    {
        return Inertia::render('Reports/TurnawayReport', [
            'startDate' => Carbon::today()->subDays(30)->toDateString(),
            'endDate' => Carbon::today()->toDateString(),
        ]);
    }

    public function generate(Request $request)
    {
        $startDate = Carbon::parse($request->get('start_date', Carbon::today()->subDays(30)));
        $endDate = Carbon::parse($request->get('end_date', Carbon::today()));
        $teamId = auth()->user()->team_id;

        $this->turnawayService->setTeamId($teamId);

        $summary = $this->turnawayService->getTurnawaySummary($startDate, $endDate);
        $byReason = $this->turnawayService->getByReason($startDate, $endDate);
        $byRoomType = $this->turnawayService->getByRoomType($startDate, $endDate);
        $dailyTrend = $this->turnawayService->getDailyTrend($startDate, $endDate);

        return response()->json([
            'summary' => $summary,
            'by_reason' => $byReason,
            'by_room_type' => $byRoomType,
            'daily_trend' => $dailyTrend,
        ]);
    }

    public function export(Request $request)
    {
        $startDate = Carbon::parse($request->get('start_date', Carbon::today()->subDays(30)));
        $endDate = Carbon::parse($request->get('end_date', Carbon::today()));
        $teamId = auth()->user()->team_id;

        $this->turnawayService->setTeamId($teamId);
        $summary = $this->turnawayService->getTurnawaySummary($startDate, $endDate);

        $filename = "turnaway_report_{$startDate->toDateString()}_to_{$endDate->toDateString()}.csv";
        $handle = fopen('php://output', 'w');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        fputcsv($handle, ['Turnaway Report']);
        fputcsv($handle, ['Period', "{$startDate->toDateString()} to {$endDate->toDateString()}"]);
        fputcsv($handle, []);
        fputcsv($handle, ['Metric', 'Value']);
        fputcsv($handle, ['Total Turnaways', $summary['total_turnaways']]);
        fputcsv($handle, ['Estimated Revenue Loss', number_format($summary['estimated_revenue_loss'], 2) . ' SAR']);
        fputcsv($handle, ['Average Room Rate', number_format($summary['average_room_rate'], 2) . ' SAR']);
        fclose($handle);
        exit;
    }
}
