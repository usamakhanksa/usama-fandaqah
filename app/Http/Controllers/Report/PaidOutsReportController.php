<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Services\Reports\PaidOutsReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaidOutsReportController extends Controller
{
    protected $paidOutsService;

    public function __construct(PaidOutsReportService $paidOutsService)
    {
        $this->paidOutsService = $paidOutsService;
    }

    public function index()
    {
        return Inertia::render('Reports/PaidOutsReport', [
            'startDate' => Carbon::today()->startOfMonth()->toDateString(),
            'endDate' => Carbon::today()->toDateString(),
        ]);
    }

    public function generate(Request $request)
    {
        $startDate = Carbon::parse($request->get('start_date', Carbon::today()->startOfMonth()));
        $endDate = Carbon::parse($request->get('end_date', Carbon::today()));
        $category = $request->get('category');

        $teamId = auth()->user()->team_id;
        $this->paidOutsService->setTeamId($teamId);

        $summary = $this->paidOutsService->getPaidOutsSummary($startDate, $endDate);
        $byCategory = $this->paidOutsService->getByCategory($startDate, $endDate);
        $byCashier = $this->paidOutsService->getByCashier($startDate, $endDate);
        $dailyTrend = $this->paidOutsService->getDailyTrend($startDate, $endDate);
        $byStatus = $this->paidOutsService->getByStatus($startDate, $endDate);

        return response()->json([
            'summary' => $summary,
            'by_category' => $byCategory,
            'by_cashier' => $byCashier,
            'daily_trend' => $dailyTrend,
            'by_status' => $byStatus,
        ]);
    }

    public function export(Request $request)
    {
        $startDate = Carbon::parse($request->get('start_date', Carbon::today()->startOfMonth()));
        $endDate = Carbon::parse($request->get('end_date', Carbon::today()));
        $teamId = auth()->user()->team_id;

        $this->paidOutsService->setTeamId($teamId);
        $summary = $this->paidOutsService->getPaidOutsSummary($startDate, $endDate);

        $filename = "paidouts_report_{$startDate->toDateString()}_to_{$endDate->toDateString()}.csv";
        $handle = fopen('php://output', 'w');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        fputcsv($handle, ['Paid-Outs Report']);
        fputcsv($handle, ['Period', "{$startDate->toDateString()} to {$endDate->toDateString()}"]);
        fputcsv($handle, []);
        fputcsv($handle, ['Metric', 'Value']);
        fputcsv($handle, ['Total Paid-Outs', number_format($summary['total_paid_outs'], 2) . ' SAR']);
        fputcsv($handle, ['Count', $summary['paid_out_count']]);
        fputcsv($handle, ['Average Paid-Out', number_format($summary['average_paid_out'], 2) . ' SAR']);
        fputcsv($handle, ['Total Revenue', number_format($summary['total_revenue'], 2) . ' SAR']);
        fputcsv($handle, ['Paid-Out to Revenue Ratio', $summary['paid_out_to_revenue_ratio'] . '%']);
        fclose($handle);
        exit;
    }
}
