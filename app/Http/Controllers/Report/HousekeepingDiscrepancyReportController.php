<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Services\Reports\HousekeepingDiscrepancyReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HousekeepingDiscrepancyReportController extends Controller
{
    protected $hkService;

    public function __construct(HousekeepingDiscrepancyReportService $hkService)
    {
        $this->hkService = $hkService;
    }

    public function index()
    {
        return Inertia::render('Reports/HousekeepingDiscrepancyReport', [
            'startDate' => Carbon::today()->subDays(7)->toDateString(),
            'endDate' => Carbon::today()->toDateString(),
        ]);
    }

    public function generate(Request $request)
    {
        $startDate = Carbon::parse($request->get('start_date', Carbon::today()->subDays(7)));
        $endDate = Carbon::parse($request->get('end_date', Carbon::today()));
        $teamId = auth()->user()->team_id;

        $this->hkService->setTeamId($teamId);

        $summary = $this->hkService->getDiscrepancySummary($startDate, $endDate);
        $mismatches = $this->hkService->getStatusMismatch($teamId);
        $uncleaned = $this->hkService->getNotCleanedAfterCheckout($startDate, $endDate, $teamId);
        $maintenance = $this->hkService->getMaintenanceBlocks($teamId);
        $cleaningTrend = $this->hkService->getDailyCleaningTimeTrend($startDate, $endDate, $teamId);
        $avgCleaningTime = $this->hkService->getAverageCleaningTime($startDate, $endDate, $teamId);

        return response()->json([
            'summary' => $summary,
            'mismatches' => $mismatches,
            'uncleaned_rooms' => $uncleaned,
            'maintenance_blocks' => $maintenance,
            'cleaning_trend' => $cleaningTrend,
            'avg_cleaning_time' => $avgCleaningTime,
        ]);
    }

    public function export(Request $request)
    {
        $startDate = Carbon::parse($request->get('start_date', Carbon::today()->subDays(7)));
        $endDate = Carbon::parse($request->get('end_date', Carbon::today()));
        $format = $request->get('format', 'csv');
        $teamId = auth()->user()->team_id;

        $this->hkService->setTeamId($teamId);
        $summary = $this->hkService->getDiscrepancySummary($startDate, $endDate);

        if ($format === 'pdf') {
            $pdf = \PDF::loadView('reports.housekeeping_discrepancy_pdf', [
                'summary' => $summary,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ]);
            return $pdf->download("hk_discrepancy_report_{$startDate->toDateString()}_to_{$endDate->toDateString()}.pdf");
        }

        // CSV export
        $filename = "hk_discrepancy_report_{$startDate->toDateString()}_to_{$endDate->toDateString()}.csv";
        $handle = fopen('php://output', 'w');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        fputcsv($handle, ['Housekeeping Discrepancy Summary']);
        fputcsv($handle, ['Period', "{$startDate->toDateString()} to {$endDate->toDateString()}"]);
        fputcsv($handle, []);
        fputcsv($handle, ['Metric', 'Value']);
        fputcsv($handle, ['Status Mismatched Rooms', $summary['mismatched_rooms']]);
        fputcsv($handle, ['Not Cleaned After Checkout', $summary['not_cleaned_after_checkout']]);
        fputcsv($handle, ['Maintenance Blocks', $summary['maintenance_blocks']]);
        fputcsv($handle, ['Average Cleaning Time (min)', $summary['avg_cleaning_time_minutes']]);
        fputcsv($handle, []);
        fputcsv($handle, ['Room Number', 'Front Desk Status', 'HK Status']);
        foreach ($summary['mismatch_details'] ?? [] as $room) {
            fputcsv($handle, [$room->room_number, $room->front_desk_status, $room->housekeeping_status]);
        }
        fclose($handle);
        exit;
    }
}
