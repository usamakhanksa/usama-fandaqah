<?php

namespace App\Services\Reports;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Source;

class SourcePerformanceReportService extends ReportService
{
    /**
     * Get source performance summary for date range
     */
    public function getSourcePerformance(Carbon $startDate, Carbon $endDate)
    {
        $teamId = $this->teamId;

        $sources = DB::table('sources')
            ->where('team_id', $teamId)
            ->withCount(['reservations as total_reservations' => function($q) use ($startDate, $endDate) {
                $q->whereDate('check_in', '>=', $startDate)
                  ->whereDate('check_in', '<=', $endDate);
            }])
            ->withSum(['reservations as total_revenue' => function($q) use ($startDate, $endDate) {
                $q->whereDate('check_in', '>=', $startDate)
                  ->whereDate('check_in', '<=', $endDate);
            }], 'total_amount')
            ->withSum(['reservations as cancelled_count' => function($q) use ($startDate, $endDate) {
                $q->where('status', 'cancelled')
                  ->whereDate('check_in', '>=', $startDate)
                  ->whereDate('check_in', '<=', $endDate);
            }], 'id')
            ->get();

        $results = [];
        foreach ($sources as $source) {
            $totalReservations = $source->total_reservations ?: 1; // avoid division by zero
            $cancelledCount = $source->cancelled_count ?: 0;
            $totalRevenue = $source->total_revenue ?: 0;
            
            $adr = $source->total_reservations > 0 ? $totalRevenue / $source->total_reservations : 0;
            $cancellationRate = ($cancelledCount / $source->total_reservations) * 100;

            $results[] = [
                'source_id' => $source->id,
                'source_name' => $source->name,
                'is_travel_agent' => $source->is_travel_agent ?? false,
                'commission_rate' => $source->commission_rate ?? 0,
                'total_reservations' => $source->total_reservations,
                'total_revenue' => $totalRevenue,
                'adr' => round($adr, 2),
                'cancellation_count' => $cancelledCount,
                'cancellation_rate' => round($cancellationRate, 2),
                'commission_amount' => round($totalRevenue * (($source->commission_rate ?? 0) / 100), 2),
            ];
        }

        return collect($results)->sortByDesc('total_revenue')->values();
    }

    /**
     * Get booking conversion rate (reservations created vs cancelled)
     */
    public function getConversionRates(Carbon $startDate, Carbon $endDate)
    {
        $teamId = $this->teamId;

        $data = DB::table('sources')
            ->where('team_id', $teamId)
            ->withCount(['reservations as total_bookings' => function($q) use ($startDate, $endDate) {
                $q->whereDate('created_at', '>=', $startDate)
                  ->whereDate('created_at', '<=', $endDate);
            }])
            ->withCount(['reservations as confirmed_bookings' => function($q) use ($startDate, $endDate) {
                $q->whereIn('status', ['confirmed', 'checked-in'])
                  ->whereDate('check_in', '>=', $startDate)
                  ->whereDate('check_in', '<=', $endDate);
            }])
            ->get();

        return $data->map(function($item) {
            $conversionRate = $item->total_bookings > 0 
                ? ($item->confirmed_bookings / $item->total_bookings) * 100 
                : 0;
            
            return [
                'source_name' => $item->name,
                'total_bookings' => $item->total_bookings,
                'confirmed_bookings' => $item->confirmed_bookings,
                'conversion_rate' => round($conversionRate, 2),
            ];
        });
    }

    /**
     * Get revenue trend by source over time
     */
    public function getRevenueTrendBySource(Carbon $startDate, Carbon $endDate)
    {
        $teamId = $this->teamId;

        return DB::table('reservations')
            ->join('sources', 'reservations.source_id', '=', 'sources.id')
            ->where('reservations.team_id', $teamId)
            ->whereIn('reservations.status', ['checked-in', 'checked-out'])
            ->whereDate('reservations.check_in', '>=', $startDate)
            ->whereDate('reservations.check_in', '<=', $endDate)
            ->select(
                'sources.name as source_name',
                DB::raw('MONTHNAME(reservations.check_in) as month'),
                DB::raw('MONTH(reservations.check_in) as month_num'),
                DB::raw('SUM(reservations.total_amount) as revenue')
            )
            ->groupBy('sources.id', 'sources.name', 'month_num', 'month')
            ->orderBy('sources.name')
            ->orderBy('month_num')
            ->get();
    }
}
