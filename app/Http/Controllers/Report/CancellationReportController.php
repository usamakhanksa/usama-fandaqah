<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Services\Reports\CancellationReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CancellationReportController extends Controller
{
    protected $cancellationService;

    public function __construct(CancellationReportService $cancellationService)
    {
        $this->cancellationService = $cancellationService;
    }

    public function index()
    {
        return Inertia::render('Reports/CancellationReport', [
            'startDate' => Carbon::today()->subDays(30)->toDateString(),
            'endDate' => Carbon::today()->toDateString(),
        ]);
    }

    public function generate(Request $request)
    {
        $startDate = Carbon::parse($request->get('start_date', Carbon::today()->subDays(30)));
        $endDate = Carbon::parse($request->get('end_date', Carbon::today()));
        $teamId = auth()->user()->team_id;

        $this->cancellationService->setTeamId($teamId);

        $stats = $this->cancellationService->getCancellationStats($startDate, $endDate);
        $bySource = $this->cancellationService->getBySource($startDate, $endDate);
        $byLeadTime = $this->cancellationService->getByLeadTime($startDate, $endDate);
        $byReason = $this->cancellationService->getByReason($startDate, $endDate);
        $byDayOfWeek = $this->cancellationService->getByDayOfWeek($startDate, $endDate);
        $bySeason = $this->cancellationService->getBySeason($startDate, $endDate);

        return response()->json([
            'stats' => $stats,
            'by_source' => $bySource,
            'by_lead_time' => $byLeadTime,
            'by_reason' => $byReason,
            'by_day_of_week' => $byDayOfWeek,
            'by_season' => $bySeason,
        ]);
    }

    public function export(Request $request)
    {
        $startDate = Carbon::parse($request->get('start_date', Carbon::today()->subDays(30)));
        $endDate = Carbon::parse($request->get('end_date', Carbon::today()));
        $format = $request->get('format', 'csv');
        $teamId = auth()->user()->team_id;

        $this->cancellationService->setTeamId($teamId);
        $stats = $this->cancellationService->getCancellationStats($startDate, $endDate);

        if ($format === 'pdf') {
            $pdf = \PDF::loadView('reports.cancellation_pdf', [
                'stats' => $stats,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ]);
            return $pdf->download("cancellation_report_{$startDate->toDateString()}_to_{$endDate->toDateString()}.pdf");
        }

        // CSV export
        $filename = "cancellation_report_{$startDate->toDateString()}_to_{$endDate->toDateString()}.csv";
        $handle = fopen('php://output', 'w');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        fputcsv($handle, ['Cancellation Statistics']);
        fputcsv($handle, ['Period', "{$startDate->toDateString()} to {$endDate->toDateString()}"]);
        fputcsv($handle, []);
        fputcsv($handle, ['Metric', 'Value']);
        fputcsv($handle, ['Cancellation Count', $stats['cancellation_count']]);
        fputcsv($handle, ['Total Reservations', $stats['total_reservations']]);
        fputcsv($handle, ['Cancellation Rate %', $stats['cancellation_rate'] . '%']);
        fputcsv($handle, ['Lost Revenue', $stats['lost_revenue']]);
        fputcsv($handle, ['Refunded Amount', $stats['refunded_amount']]);
        fputcsv($handle, ['Net Loss', $stats['net_loss']]);
        fclose($handle);
        exit;
    }
}
