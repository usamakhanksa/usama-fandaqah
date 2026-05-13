<?php

namespace App\Services\Reports;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Reservation;

class NoShowReportService extends ReportService
{
    /**
     * Get no-show statistics by date range
     */
    public function getNoShowStats(Carbon $startDate, Carbon $endDate)
    {
        $teamId = $this->teamId;

        $noShows = Reservation::where('team_id', $teamId)
            ->where('noshow_flag', true)
            ->whereDate('check_in', '>=', $startDate)
            ->whereDate('check_in', '<=', $endDate)
            ->with(['source', 'unit.unitType', 'guest'])
            ->get();

        $totalReservations = Reservation::where('team_id', $teamId)
            ->whereDate('check_in', '>=', $startDate)
            ->whereDate('check_in', '<=', $endDate)
            ->count();

        $noShowCount = $noShows->count();
        $noShowRate = $totalReservations > 0 ? ($noShowCount / $totalReservations) * 100 : 0;

        // Revenue impact
        $totalPotentialRevenue = $noShows->sum('total_amount');
        $collectedCharges = $noShows->sum('no_show_charge') ?? 0;
        $lostRevenue = $totalPotentialRevenue - $collectedCharges;

        return [
            'no_show_count' => $noShowCount,
            'total_reservations' => $totalReservations,
            'no_show_rate' => round($noShowRate, 2),
            'potential_revenue' => $totalPotentialRevenue,
            'collected_charges' => $collectedCharges,
            'lost_revenue' => $lostRevenue,
            'charge_percentage' => $totalPotentialRevenue > 0 ? round(($collectedCharges / $totalPotentialRevenue) * 100, 2) : 0,
            'details' => $noShows,
        ];
    }

    /**
     * Get no-show breakdown by source
     */
    public function getBySource(Carbon $startDate, Carbon $endDate)
    {
        $teamId = $this->teamId;

        return DB::table('reservations')
            ->join('sources', 'reservations.source_id', '=', 'sources.id')
            ->where('reservations.team_id', $teamId)
            ->where('reservations.noshow_flag', true)
            ->whereDate('reservations.check_in', '>=', $startDate)
            ->whereDate('reservations.check_in', '<=', $endDate)
            ->select(
                'sources.name',
                DB::raw('COUNT(*) as no_show_count'),
                DB::raw('SUM(reservations.total_amount) as potential_revenue'),
                DB::raw('SUM(reservations.no_show_charge) as collected_charges')
            )
            ->groupBy('sources.id', 'sources.name')
            ->orderByDesc('no_show_count')
            ->get();
    }

    /**
     * Get no-show breakdown by room type
     */
    public function getByRoomType(Carbon $startDate, Carbon $endDate)
    {
        $teamId = $this->teamId;

        return DB::table('reservations')
            ->join('units', 'reservations.unit_id', '=', 'units.id')
            ->join('unit_types', 'units.unit_type_id', '=', 'unit_types.id')
            ->where('reservations.team_id', $teamId)
            ->where('reservations.noshow_flag', true)
            ->whereDate('reservations.check_in', '>=', $startDate)
            ->whereDate('reservations.check_in', '<=', $endDate)
            ->select(
                'unit_types.name',
                DB::raw('COUNT(*) as no_show_count'),
                DB::raw('SUM(reservations.total_amount) as potential_revenue')
            )
            ->groupBy('unit_types.id', 'unit_types.name')
            ->orderByDesc('no_show_count')
            ->get();
    }

    /**
     * Get no-show breakdown by day of week
     */
    public function getByDayOfWeek(Carbon $startDate, Carbon $endDate)
    {
        $teamId = $this->teamId;

        return DB::table('reservations')
            ->where('team_id', $teamId)
            ->where('noshow_flag', true)
            ->whereDate('check_in', '>=', $startDate)
            ->whereDate('check_in', '<=', $endDate)
            ->select(
                DB::raw('DAYNAME(check_in) as day_name'),
                DB::raw('DAYOFWEEK(check_in) as day_number'),
                DB::raw('COUNT(*) as no_show_count'),
                DB::raw('SUM(total_amount) as potential_revenue')
            )
            ->groupBy('day_name', 'day_number')
            ->orderBy('day_number')
            ->get();
    }

    /**
     * Charge percentage analysis
     */
    public function getChargePercentageAnalysis(Carbon $startDate, Carbon $endDate)
    {
        $teamId = $this->teamId;

        $noShows = Reservation::where('team_id', $teamId)
            ->where('noshow_flag', true)
            ->whereDate('check_in', '>=', $startDate)
            ->whereDate('check_in', '<=', $endDate)
            ->get();

        $withCharge = $noShows->whereNotNull('no_show_charge')->where('no_show', '>', 0);
        $withoutCharge = $noShows->where(function($q) {
            $q->whereNull('no_show_charge')->orWhere('no_show_charge', 0);
        });

        return [
            'total_no_shows' => $noShows->count(),
            'with_charge' => $withCharge->count(),
            'without_charge' => $withoutCharge->count(),
            'charge_applied_percentage' => $noShows->count() > 0 ? round(($withCharge->count() / $noShows->count()) * 100, 2) : 0,
        ];
    }
}
