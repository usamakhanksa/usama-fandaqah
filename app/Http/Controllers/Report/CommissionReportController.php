<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Services\Reports\CommissionReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CommissionReportController extends Controller
{
    protected $commissionService;

    public function __construct(CommissionReportService $commissionService)
    {
        $this->commissionService = $commissionService;
    }

    public function index()
    {
        return Inertia::render('Reports/CommissionReport', [
            'startDate' => Carbon::today()->startOfMonth()->toDateString(),
            'endDate' => Carbon::today()->toDateString(),
        ]);
    }

    public function generate(Request $request)
    {
        $startDate = Carbon::parse($request->get('start_date', Carbon::today()->startOfMonth()));
        $endDate = Carbon::parse($request->get('end_date', Carbon::today()));
        $sourceId = $request->get('source_id');
        $teamId = auth()->user()->team_id;

        $this->commissionService->setTeamId($teamId);

        $summary = $this->commissionService->getCommissionSummary($startDate, $endDate);
        $byReservation = $this->commissionService->getCommissionByReservation($startDate, $endDate, $sourceId);
        $paidUnpaid = $this->commissionService->getPaidUnpaidSummary($startDate, $endDate);
        $rateComparison = $this->commissionService->getCommissionRateComparison();

        return response()->json([
            'summary' => $summary,
            'by_reservation' => $byReservation,
            'paid_unpaid' => $paidUnpaid,
            'rate_comparison' => $rateComparison,
        ]);
    }

    public function export(Request $request)
    {
        $startDate = Carbon::parse($request->get('start_date', Carbon::today()->startOfMonth()));
        $endDate = Carbon::parse($request->get('end_date', Carbon::today()));
        $format = $request->get('format', 'csv');
        $teamId = auth()->user()->team_id;

        $this->commissionService->setTeamId($teamId);
        $summary = $this->commissionService->getCommissionSummary($startDate, $endDate);

        if ($format === 'pdf') {
            $pdf = \PDF::loadView('reports.commission_pdf', [
                'summary' => $summary,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ]);
            return $pdf->download("commission_report_{$startDate->toDateString()}_to_{$endDate->toDateString()}.pdf");
        }

        // CSV export
        $filename = "commission_report_{$startDate->toDateString()}_to_{$endDate->toDateString()}.csv";
        $handle = fopen('php://output', 'w');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        fputcsv($handle, ['Source', 'Commission Rate %', 'Reservations', 'Total Revenue', 'Commission Amount', 'Paid Amount', 'Unpaid Amount']);
        foreach ($summary as $row) {
            fputcsv($handle, [
                $row['source_name'],
                $row['commission_rate'] . '%',
                $row['reservation_count'],
                $row['total_revenue'],
                $row['commission_amount'],
                $row['paid_amount'],
                $row['unpaid_amount'],
            ]);
        }
        fclose($handle);
        exit;
    }
}
