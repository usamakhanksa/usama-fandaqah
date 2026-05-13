<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Services\Reports\NoShowReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NoShowReportController extends Controller
{
    protected $noShowService;

    public function __construct(NoShowReportService $noShowService)
    {
        $this->noShowService = $noShowService;
    }

    public function index()
    {
        return Inertia::render('Reports/NoShowReport', [
            'startDate' => Carbon::today()->subDays(30)->toDateString(),
            'endDate' => Carbon::today()->toDateString(),
        ]);
    }

    public function generate(Request $request)
    {
        $startDate = Carbon::parse($request->get('start_date', Carbon::today()->subDays(30)));
        $endDate = Carbon::parse($request->get('end_date', Carbon::today()));
        $teamId = auth()->user()->team_id;

        $this->noShowService->setTeamId($teamId);

        $stats = $this->noShowService->getNoShowStats($startDate, $endDate);
        $bySource = $this->noShowService->getBySource($startDate, $endDate);
        $byRoomType = $this->noShowService->getByRoomType($startDate, $endDate);
        $byDayOfWeek = $this->noShowService->getByDayOfWeek($startDate, $endDate);
        $chargeAnalysis = $this->noShowService->getChargePercentageAnalysis($startDate, $endDate);

        return response()->json([
            'stats' => $stats,
            'by_source' => $bySource,
            'by_room_type' => $byRoomType,
            'by_day_of_week' => $byDayOfWeek,
            'charge_analysis' => $chargeAnalysis,
        ]);
    }

    public function export(Request $request)
    {
        $startDate = Carbon::parse($request->get('start_date', Carbon::today()->subDays(30)));
        $endDate = Carbon::parse($request->get('end_date', Carbon::today()));
        $format = $request->get('format', 'csv');
        $teamId = auth()->user()->team_id;

        $this->noShowService->setTeamId($teamId);
        $stats = $this->noShowService->getNoShowStats($startDate, $endDate);

        if ($format === 'pdf') {
            $pdf = \PDF::loadView('reports.no_show_pdf', [
                'stats' => $stats,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ]);
            return $pdf->download("no_show_report_{$startDate->toDateString()}_to_{$endDate->toDateString()}.pdf");
        }

        // CSV export
        $filename = "no_show_report_{$startDate->toDateString()}_to_{$endDate->toDateString()}.csv";
        $handle = fopen('php://output', 'w');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        fputcsv($handle, ['No-Show Statistics']);
        fputcsv($handle, ['Period', "{$startDate->toDateString()} to {$endDate->toDateString()}"]);
        fputcsv($handle, []);
        fputcsv($handle, ['Metric', 'Value']);
        fputcsv($handle, ['No-Show Count', $stats['no_show_count']]);
        fputcsv($handle, ['Total Reservations', $stats['total_reservations']]);
        fputcsv($handle, ['No-Show Rate %', $stats['no_show_rate'] . '%']);
        fputcsv($handle, ['Potential Revenue', $stats['potential_revenue']]);
        fputcsv($handle, ['Collected Charges', $stats['collected_charges']]);
        fputcsv($handle, ['Lost Revenue', $stats['lost_revenue']]);
        fputcsv($handle, ['Charge Percentage', $stats['charge_percentage'] . '%']);
        fclose($handle);
        exit;
    }
}
