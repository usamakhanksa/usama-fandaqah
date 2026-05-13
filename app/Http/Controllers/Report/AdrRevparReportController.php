<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Services\Reports\AdrRevparCalculationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdrRevparReportController extends Controller
{
    protected $adrRevparService;

    public function __construct(AdrRevparCalculationService $adrRevparService)
    {
        $this->adrRevparService = $adrRevparService;
    }

    public function index()
    {
        return Inertia::render('Reports/AdrRevparReport', [
            'startDate' => Carbon::today()->startOfMonth()->toDateString(),
            'endDate' => Carbon::today()->toDateString(),
            'roomTypes' => $this->adrRevparService->getRoomTypes(),
        ]);
    }

    public function generate(Request $request)
    {
        $startDate = Carbon::parse($request->get('start_date', Carbon::today()->startOfMonth()));
        $endDate = Carbon::parse($request->get('end_date', Carbon::today()));
        $roomTypeId = $request->get('room_type_id');
        $aggregation = $request->get('aggregation', 'daily'); // daily, weekly, monthly
        $movingAverage = $request->get('moving_average'); // 7, 30, or null

        $this->adrRevparService->setTeamId(auth()->user()->team_id);

        switch ($aggregation) {
            case 'weekly':
                $data = $this->adrRevparService->getWeeklyAdrRevpar($startDate, $endDate, $roomTypeId);
                break;
            case 'monthly':
                $data = $this->adrRevparService->getMonthlyAdrRevpar($startDate, $endDate, $roomTypeId);
                break;
            default:
                $data = $this->adrRevparService->getDailyAdrRevpar($startDate, $endDate, $roomTypeId);
                break;
        }

        $response = [
            'daily' => $data,
            'aggregation' => $aggregation,
        ];

        if ($movingAverage && $aggregation === 'daily') {
            $response['ma_adr_7'] = $this->adrRevparService->getMovingAverage($data, 'adr', 7);
            $response['ma_adr_30'] = $this->adrRevparService->getMovingAverage($data, 'adr', 30);
            $response['ma_revpar_7'] = $this->adrRevparService->getMovingAverage($data, 'revpar', 7);
            $response['ma_revpar_30'] = $this->adrRevparService->getMovingAverage($data, 'revpar', 30);
        }

        return response()->json($response);
    }

    public function export(Request $request)
    {
        $startDate = Carbon::parse($request->get('start_date', Carbon::today()->startOfMonth()));
        $endDate = Carbon::parse($request->get('end_date', Carbon::today()));
        $format = $request->get('format', 'csv');
        $roomTypeId = $request->get('room_type_id');

        $this->adrRevparService->setTeamId(auth()->user()->team_id);
        $data = $this->adrRevparService->getDailyAdrRevpar($startDate, $endDate, $roomTypeId);

        $filename = "adr_revpar_report_{$startDate->format('Y-m-d')}_to_{$endDate->format('Y-m-d')}.csv";
        $handle = fopen('php://output', 'w');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        fputcsv($handle, ['Date', 'Rooms Sold', 'Room Revenue', 'ADR', 'Total Rooms', 'RevPAR']);

        foreach ($data as $row) {
            fputcsv($handle, [
                $row['date'],
                $row['rooms_sold'],
                $row['room_revenue'],
                $row['adr'],
                $row['total_rooms'],
                $row['revpar'],
            ]);
        }

        fclose($handle);
        exit;
    }
}
