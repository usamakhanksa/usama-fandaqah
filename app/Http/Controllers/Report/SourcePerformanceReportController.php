<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Services\Reports\SourcePerformanceReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SourcePerformanceReportController extends Controller
{
    protected $sourcePerformanceService;

    public function __construct(SourcePerformanceReportService $sourcePerformanceService)
    {
        $this->sourcePerformanceService = $sourcePerformanceService;
    }

    public function index()
    {
        return Inertia::render('Reports/SourcePerformanceReport', [
            'startDate' => Carbon::today()->startOfMonth()->toDateString(),
            'endDate' => Carbon::today()->toDateString(),
        ]);
    }

    public function generate(Request $request)
    {
        $startDate = Carbon::parse($request->get('start_date', Carbon::today()->startOfMonth()));
        $endDate = Carbon::parse($request->get('end_date', Carbon::today()));
        $teamId = auth()->user()->team_id;

        $this->sourcePerformanceService->setTeamId($teamId);

        $performance = $this->sourcePerformanceService->getSourcePerformance($startDate, $endDate);
        $conversionRates = $this->sourcePerformanceService->getConversionRates($startDate, $endDate);
        $revenueTrend = $this->sourcePerformanceService->getRevenueTrendBySource($startDate, $endDate);

        return response()->json([
            'performance' => $performance,
            'conversion_rates' => $conversionRates,
            'revenue_trend' => $revenueTrend,
        ]);
    }

    public function export(Request $request)
    {
        $startDate = Carbon::parse($request->get('start_date', Carbon::today()->startOfMonth()));
        $endDate = Carbon::parse($request->get('end_date', Carbon::today()));
        $teamId = auth()->user()->team_id;

        $this->sourcePerformanceService->setTeamId($teamId);
        $performance = $this->sourcePerformanceService->getSourcePerformance($startDate, $endDate);

        $filename = "source_performance_report_{$startDate->toDateString()}_to_{$endDate->toDateString()}.csv";
        $handle = fopen('php://output', 'w');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        fputcsv($handle, ['Source Performance Report']);
        fputcsv($handle, ['Period', "{$startDate->toDateString()} to {$endDate->toDateString()}"]);
        fputcsv($handle, []);
        fputcsv($handle, ['Source', 'Reservations', 'Revenue', 'ADR', 'Cancellation Rate %', 'Commission %', 'Commission Amount']);
        foreach ($performance as $row) {
            fputcsv($handle, [
                $row['source_name'],
                $row['total_reservations'],
                number_format($row['total_revenue'], 2),
                number_format($row['adr'], 2),
                $row['cancellation_rate'] . '%',
                $row['commission_rate'] . '%',
                number_format($row['commission_amount'], 2),
            ]);
        }
        fclose($handle);
        exit;
    }
}
