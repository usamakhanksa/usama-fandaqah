<?php

namespace App\Services\Reports;

use App\Models\Reservation;
use App\Models\Transaction;
use App\Models\Unit;
use App\Models\UnitType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdrRevparCalculationService extends ReportService
{
    public function getDailyAdrRevpar(Carbon $startDate, Carbon $endDate, $roomTypeId = null)
    {
        $daily = [];
        $current = $startDate->copy();

        $totalUnits = $this->getTotalAvailableRooms($roomTypeId);

        while ($current->lte($endDate)) {
            $stats = $this->computeDailyMetrics($current, $roomTypeId, $totalUnits);
            $daily[] = $stats;
            $current->addDay();
        }

        return $daily;
    }

    public function getWeeklyAdrRevpar(Carbon $startDate, Carbon $endDate, $roomTypeId = null)
    {
        $daily = $this->getDailyAdrRevpar($startDate, $endDate, $roomTypeId);
        $weekly = [];
        $weekAccum = [];

        foreach ($daily as $day) {
            $weekKey = Carbon::parse($day['date'])->startOfWeek()->toDateString();

            if (!isset($weekAccum[$weekKey])) {
                $weekAccum[$weekKey] = [
                    'week_start' => $weekKey,
                    'week_end' => Carbon::parse($weekKey)->endOfWeek()->toDateString(),
                    'rooms_sold' => 0,
                    'room_revenue' => 0,
                    'total_available' => $day['total_rooms'],
                    'days' => 0,
                ];
            }

            $weekAccum[$weekKey]['rooms_sold'] += $day['rooms_sold'];
            $weekAccum[$weekKey]['room_revenue'] += $day['room_revenue'];
            $weekAccum[$weekKey]['days']++;
        }

        foreach ($weekAccum as $key => $week) {
            $weekly[] = [
                'period' => "{$week['week_start']} - {$week['week_end']}",
                'week_start' => $week['week_start'],
                'week_end' => $week['week_end'],
                'rooms_sold' => $week['rooms_sold'],
                'room_revenue' => round($week['room_revenue'], 2),
                'adr' => $week['rooms_sold'] > 0 ? round($week['room_revenue'] / $week['rooms_sold'], 2) : 0,
                'total_rooms' => $week['total_available'],
                'revpar' => $week['days'] > 0 && $week['total_available'] > 0 ? round($week['room_revenue'] / ($week['total_available'] * $week['days']), 2) : 0,
            ];
        }

        return $weekly;
    }

    public function getMonthlyAdrRevpar(Carbon $startDate, Carbon $endDate, $roomTypeId = null)
    {
        $daily = $this->getDailyAdrRevpar($startDate, $endDate, $roomTypeId);
        $monthly = [];
        $monthAccum = [];

        foreach ($daily as $day) {
            $monthKey = Carbon::parse($day['date'])->format('Y-m');

            if (!isset($monthAccum[$monthKey])) {
                $monthAccum[$monthKey] = [
                    'month' => $monthKey,
                    'rooms_sold' => 0,
                    'room_revenue' => 0,
                    'total_available' => $day['total_rooms'],
                    'days' => 0,
                ];
            }

            $monthAccum[$monthKey]['rooms_sold'] += $day['rooms_sold'];
            $monthAccum[$monthKey]['room_revenue'] += $day['room_revenue'];
            $monthAccum[$monthKey]['days']++;
        }

        foreach ($monthAccum as $key => $month) {
            $monthly[] = [
                'period' => $month['month'],
                'month' => $month['month'],
                'rooms_sold' => $month['rooms_sold'],
                'room_revenue' => round($month['room_revenue'], 2),
                'adr' => $month['rooms_sold'] > 0 ? round($month['room_revenue'] / $month['rooms_sold'], 2) : 0,
                'total_rooms' => $month['total_available'],
                'revpar' => $month['days'] > 0 && $month['total_available'] > 0 ? round($month['room_revenue'] / ($month['total_available'] * $month['days']), 2) : 0,
            ];
        }

        return $monthly;
    }

    public function computeDailyMetrics(Carbon $date, $roomTypeId = null, $totalUnits = null)
    {
        if ($totalUnits === null) {
            $totalUnits = $this->getTotalAvailableRooms($roomTypeId);
        }

        $roomsSold = $this->getRoomsSold($date, $roomTypeId);
        $roomRevenue = $this->getRoomRevenue($date, $roomTypeId);

        $adr = $roomsSold > 0 ? round($roomRevenue / $roomsSold, 2) : 0;
        $revpar = $totalUnits > 0 ? round($roomRevenue / $totalUnits, 2) : 0;

        return [
            'date' => $date->toDateString(),
            'rooms_sold' => $roomsSold,
            'room_revenue' => round($roomRevenue, 2),
            'adr' => $adr,
            'total_rooms' => $totalUnits,
            'revpar' => $revpar,
        ];
    }

    protected function getRoomsSold(Carbon $date, $roomTypeId = null)
    {
        $query = Reservation::where('team_id', $this->teamId)
            ->where('status', 'checked-in')
            ->whereDate('check_in', '<=', $date)
            ->whereDate('check_out', '>', $date);

        if ($roomTypeId) {
            $query->whereHas('unit', function ($q) use ($roomTypeId) {
                $q->where('unit_type_id', $roomTypeId);
            });
        }

        return $query->count();
    }

    protected function getRoomRevenue(Carbon $date, $roomTypeId = null)
    {
        $query = Transaction::where('team_id', $this->teamId)
            ->where('kind', 'payment')
            ->where(function ($q) {
                $q->where('payable_type', Reservation::class)
                    ->orWhere('meta->category', 'reservation');
            })
            ->whereDate('created_at', $date);

        if ($roomTypeId) {
            $query->whereHasMorph('payable', [Reservation::class], function ($q) use ($roomTypeId) {
                $q->whereHas('unit', function ($uq) use ($roomTypeId) {
                    $uq->where('unit_type_id', $roomTypeId);
                });
            });
        }

        return (float) $query->sum('amount');
    }

    protected function getTotalAvailableRooms($roomTypeId = null)
    {
        $query = Unit::where('team_id', $this->teamId);

        if ($roomTypeId) {
            $query->where('unit_type_id', $roomTypeId);
        }

        return $query->count();
    }

    public function getMovingAverage(array $dailyData, string $metric, int $window = 7)
    {
        $result = [];

        foreach ($dailyData as $i => $day) {
            if ($i < $window - 1) {
                $result[] = [
                    'date' => $day['date'],
                    $metric => null,
                ];
                continue;
            }

            $sum = 0;
            for ($j = $i - $window + 1; $j <= $i; $j++) {
                $sum += $dailyData[$j][$metric];
            }

            $result[] = [
                'date' => $day['date'],
                $metric => round($sum / $window, 2),
            ];
        }

        return $result;
    }

    public function getRoomTypes()
    {
        return UnitType::where('team_id', $this->teamId)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
    }
}