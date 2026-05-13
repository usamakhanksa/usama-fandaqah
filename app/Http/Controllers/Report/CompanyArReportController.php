<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Services\Reports\CompanyArReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CompanyArReportController extends Controller
{
    protected $arService;

    public function __construct(CompanyArReportService $arService)
    {
        $this->arService = $arService;
    }

    public function index()
    {
        return Inertia::render('Reports/CompanyArReport', [
            'asOfDate' => Carbon::today()->toDateString(),
        ]);
    }

    public function generate(Request $request)
    {
        $asOfDate = Carbon::parse($request->get('as_of_date', Carbon::today()));
        $teamId = auth()->user()->team_id;

        $this->arService->setTeamId($teamId);

        $arSummary = $this->arService->getArSummary($asOfDate);
        $agingSummary = $this->arService->getAgingSummary($asOfDate);
        $turnover = $this->arService->getArTurnoverRatio(
            Carbon::parse($asOfDate)->startOfMonth(),
            $asOfDate
        );

        return response()->json([
            'ar_summary' => $arSummary,
            'aging_summary' => $agingSummary,
            'turnover_ratio' => $turnover,
        ]);
    }

    public function export(Request $request)
    {
        $asOfDate = Carbon::parse($request->get('as_of_date', Carbon::today()));
        $teamId = auth()->user()->team_id;

        $this->arService->setTeamId($teamId);
        $arSummary = $this->arService->getArSummary($asOfDate);

        $filename = "company_ar_report_{$asOfDate->toDateString()}.csv";
        $handle = fopen('php://output', 'w');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        fputcsv($handle, ['Company Accounts Receivable Report']);
        fputcsv($handle, ['As of Date', $asOfDate->toDateString()]);
        fputcsv($handle, []);
        fputcsv($handle, ['Company', 'Current', '30 Days', '60 Days', '90 Days', '120+ Days', 'Total Outstanding']);
        foreach ($arSummary as $row) {
            fputcsv($handle, [
                $row['company_name'],
                number_format($row['current'], 2),
                number_format($row['days_30'], 2),
                number_format($row['days_60'], 2),
                number_format($row['days_90'], 2),
                number_format($row['days_120_plus'], 2),
                number_format($row['total_outstanding'], 2),
            ]);
        }
        fclose($handle);
        exit;
    }
}
