<?php

namespace App\Services\Reports;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Reservation;
use App\Models\Unit;

class ForecastReportService extends ReportService
{
    /**
     * Get occupancy forecast for future dates
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param int $intervalDays 7, 14, 30, 90
     * @return array
     */
    public function getForecast(Carbon $startDate, Carbon $endDate, $intervalDays = 30)
    {
        $teamId = $this->teamId;
        $forecast = [];

        // Get total rooms capacity
        $totalRooms = DB::table('rooms')
            ->where('team_id', $teamId)
            ->count();

        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            $dateStr = $current->toDateString();

            // Get confirmed reservations for this date
            $confirmedCount = Reservation::where('team_id', $teamId)
                ->where('status', 'confirmed')
                ->whereDate('check_in', '<=', $current)
                ->whereDate('check_out', '>', $current)
                ->count();

            // Get checked-in reservations (actual)
            $actualOccupied = Reservation::where('team_id', $teamId)
                ->where('status', 'checked-in')
                ->whereDate('check_in', '<=', $current)
                ->whereDate('check_out', '>', $current)
                ->count();

            // Calculate forecast percentage
            $forecastPercentage = $totalRooms > 0 ? ($confirmedCount / $totalRooms) * 100 : 0;
            $actualPercentage = $totalRooms > 0 ? ($actualOccupied / $totalRooms) * 100 : 0;

            $forecast[] = [
                'date' => $dateStr,
                'total_rooms' => $totalRooms,
                'confirmed_reservations' => $confirmedCount,
                'forecast_occupancy' => round($forecastPercentage, 2),
                'actual_occupancy' => round($actualPercentage, 2),
                'variance' => round($forecastPercentage - $actualPercentage, 2),
            ];

            $current->addDay();
        }

        return $forecast;
    }

    /**
     * Get forecast summary by period (7, 14, 30, 90 days)
     */
    public function getForecastByPeriod()
    {
        $today = Carbon::today();
        $periods = [7, 14, 30, 90];
        $summary = [];

        foreach ($periods as $days) {
            $endDate = $today->copy()->addDays($days);
            $forecastData = $this->getForecast($today, $endDate, $days);

            $avgForecast = collect($forecastData)->avg('forecast_occupancy');
            $maxForecast = collect($forecastData)->max('forecast_occupancy');
            $minForecast = collect($forecastData)->min('forecast_occupancy');

            $summary[] = [
                'period_days' => $days,
                'average_occupancy' => round($avgForecast, 2),
                'peak_occupancy' => round($maxForecast, 2),
                'lowest_occupancy' => round($minForecast, 2),
                'data_points' => count($forecastData),
            ];
        }

        return $summary;
    }

    /**
     * Get forecast vs actual comparison for past dates
     */
    public function getForecastVsActual(Carbon $startDate, Carbon $endDate)
    {
        $teamId = $this->teamId;

        // Get historical data where we have both forecast (based on reservations that existed then)
        // and actual occupancy
        $data = [];

        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            $dateStr = $current->toDateString();

            // Actual occupancy on that date
            $actualOccupied = Reservation::where('team_id', $teamId)
                ->where('status', 'checked-in')
                ->whereDate('check_in', '<=', $current)
                ->whereDate('check_out', '>', $current)
                ->count();

            // What was the forecast on that date? (reservations that were confirmed with check_in <= date < check_out)
            // This is approximated by looking at reservations that were not cancelled/no-show
            $forecasted = Reservation::where('team_id', $teamId)
                ->whereIn('status', ['confirmed', 'checked-in'])
                ->whereDate('check_in', '<=', $current)
                ->whereDate('check_out', '>', $current)
                ->count();

            $totalRooms = DB::table('rooms')->where('team_id', $teamId)->count();

            $data[] = [
                'date' => $dateStr,
                'forecast_occupancy' => $totalRooms > 0 ? round(($forecasted / $totalRooms) * 100, 2) : 0,
                'actual_occupancy' => $totalRooms > 0 ? round(($actualOccupied / $totalRooms) * 100, 2) : 0,
                'forecast_rooms' => $forecasted,
                'actual_rooms' => $actualOccupied,
                'variance' => $forecasted - $actualOccupied,
            ];

            $current->addDay();
        }

        return $data;
    }
}
