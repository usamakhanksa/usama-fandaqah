<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Services\Reports\ForecastReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ForecastReportController extends Controller
{
    protected $forecastService;

    public function __construct(ForecastReportService $forecastService)
    {
        $this->forecastService = $forecastService;
    }

    public function index()
    {
        return Inertia::render('Reports/ForecastReport', [
            'initialPeriod' => 30,
            'startDate' => Carbon::today()->toDateString(),
            'endDate' => Carbon::today()->addDays(30)->toDateString(),
        ]);
    }

    public function generate(Request $request)
    {
        $startDate = Carbon::parse($request->get('start_date', Carbon::today()));
        $endDate = Carbon::parse($request->get('end_date', Carbon::today()->addDays(30)));
        $teamId = auth()->user()->team_id;

        $this->forecastService->setTeamId($teamId);

        $forecastData = $this->forecastService->getForecast($startDate, $endDate);
        $periodSummary = $this->forecastService->getForecastByPeriod();

        return response()->json([
            'forecast' => $forecastData,
            'period_summary' => $periodSummary,
        ]);
    }

    public function export(Request $request)
    {
        $startDate = Carbon::parse($request->get('start_date', Carbon::today()));
        $endDate = Carbon::parse($request->get('end_date', Carbon::today()->addDays(30)));
        $format = $request->get('format', 'csv');
        $teamId = auth()->user()->team_id;

        $this->forecastService->setTeamId($teamId);
        $data = $this->forecastService->getForecast($startDate, $endDate);

        if ($format === 'pdf') {
            $pdf = \PDF::loadView('reports.forecast_pdf', [
                'forecast' => $data,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ]);
            return $pdf->download("forecast_report_{$startDate->toDateString()}_to_{$endDate->toDateString()}.pdf");
        }

        // CSV export
        $filename = "forecast_report_{$startDate->toDateString()}_to_{$endDate->toDateString()}.csv";
        $handle = fopen('php://output', 'w');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        fputcsv($handle, ['Date', 'Total Rooms', 'Confirmed Reservations', 'Forecast Occupancy %', 'Actual Occupancy %', 'Variance']);
        foreach ($data as $row) {
            fputcsv($handle, [
                $row['date'],
                $row['total_rooms'],
                $row['confirmed_reservations'],
                $row['forecast_occupancy'] . '%',
                $row['actual_occupancy'] . '%',
                $row['variance'] . '%',
            ]);
        }
        fclose($handle);
        exit;
    }
}
