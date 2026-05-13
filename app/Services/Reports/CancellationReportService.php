<?php

namespace App\Services\Reports;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Reservation;

class CancellationReportService extends ReportService
{
    /**
     * Get cancellation statistics by date range
     */
    public function getCancellationStats(Carbon $startDate, Carbon $endDate)
    {
        $teamId = $this->teamId;

        $cancellations = Reservation::where('team_id', $teamId)
            ->where('status', 'cancelled')
            ->whereDate('cancelled_at', '>=', $startDate)
            ->whereDate('cancelled_at', '<=', $endDate)
            ->with(['source', 'unit.unitType'])
            ->get();

        $totalReservations = Reservation::where('team_id', $teamId)
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->count();

        $cancellationCount = $cancellations->count();
        $cancellationRate = $totalReservations > 0 ? ($cancellationCount / $totalReservations) * 100 : 0;

        $totalLostRevenue = $cancellations->sum('total_amount');
        $refundedAmount = $cancellations->sum('refund_amount') ?? 0;
        $netLoss = $totalLostRevenue - $refundedAmount;

        return [
            'cancellation_count' => $cancellationCount,
            'total_reservations' => $totalReservations,
            'cancellation_rate' => round($cancellationRate, 2),
            'lost_revenue' => $totalLostRevenue,
            'refunded_amount' => $refundedAmount,
            'net_loss' => $netLoss,
            'details' => $cancellations,
        ];
    }

    public function getBySource(Carbon $startDate, Carbon $endDate)
    {
        $teamId = $this->teamId;

        return DB::table('reservations')
            ->join('sources', 'reservations.source_id', '=', 'sources.id')
            ->where('reservations.team_id', $teamId)
            ->where('reservations.status', 'cancelled')
            ->whereDate('reservations.cancelled_at', '>=', $startDate)
            ->whereDate('reservations.cancelled_at', '<=', $endDate)
            ->select(
                'sources.name',
                DB::raw('COUNT(*) as cancellation_count'),
                DB::raw('SUM(reservations.total_amount) as lost_revenue')
            )
            ->groupBy('sources.id', 'sources.name')
            ->orderByDesc('cancellation_count')
            ->get();
    }

    public function getByLeadTime(Carbon $startDate, Carbon $endDate)
    {
        $teamId = $this->teamId;

        $cancellations = Reservation::where('team_id', $teamId)
            ->where('status', 'cancelled')
            ->whereDate('cancelled_at', '>=', $startDate)
            ->whereDate('cancelled_at', '<=', $endDate)
            ->select(
                DB::raw('DATEDIFF(check_in, cancelled_at) as lead_time_days'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total_amount) as total_amount')
            )
            ->groupBy('lead_time_days')
            ->orderBy('lead_time_days')
            ->get();

        $totalReservations = Reservation::where('team_id', $teamId)
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->count();

        $cancellationCount = $cancellations->count();
        $cancellationRate = $totalReservations > 0 ? ($cancellationCount / $totalReservations) * 100 : 0;

        // Revenue impact
        $totalLostRevenue = $cancellations->sum('total_amount');
        $refundedAmount = $cancellations->sum('refund_amount') ?? 0;
        $netLoss = $totalLostRevenue - $refundedAmount;

        return [
            'cancellation_count' => $cancellationCount,
            'total_reservations' => $totalReservations,
            'cancellation_rate' => round($cancellationRate, 2),
            'lost_revenue' => $totalLostRevenue,
            'refunded_amount' => $refundedAmount,
            'net_loss' => $netLoss,
            'details' => $cancellations,
        ];
    }

    /**
     * Get cancellation breakdown by source
     */
    public function getBySource(Carbon $startDate, Carbon $endDate)
    {
        $teamId = $this->teamId;

        return DB::table('reservations')
            ->join('sources', 'reservations.source_id', '=', 'sources.id')
            ->where('reservations.team_id', $teamId)
            ->where('reservations.status', 'cancelled')
            ->whereDate('reservations.updated_at', '>=', $startDate)
            ->whereDate('reservations.updated_at', '<=', $endDate)
            ->select(
                'sources.name',
                DB::raw('COUNT(*) as cancellation_count'),
                DB::raw('SUM(reservations.total_amount) as lost_revenue')
            )
            ->groupBy('sources.id', 'sources.name')
            ->orderByDesc('cancellation_count')
            ->get();
    }

    /**
     * Get cancellation breakdown by lead time (days before arrival)
     */
    public function getByLeadTime(Carbon $startDate, Carbon $endDate)
    {
        $teamId = $this->teamId;

        $cancellations = Reservation::where('team_id', $teamId)
            ->where('status', 'cancelled')
            ->whereDate('updated_at', '>=', $startDate)
            ->whereDate('updated_at', '<=', $endDate)
            ->select(
                DB::raw('DATEDIFF(check_in, updated_at) as lead_time_days'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total_amount) as total_amount')
            )
            ->groupBy('lead_time_days')
            ->orderBy('lead_time_days')
            ->get();

        // Categorize into buckets
        $buckets = [
            'Same Day' => 0,
            '1-3 Days' => 0,
            '4-7 Days' => 0,
            '1-2 Weeks' => 0,
            '3-4 Weeks' => 0,
            '1+ Month' => 0,
        ];

        foreach ($cancellations as $item) {
            $days = $item->lead_time_days;
            if ($days <= 0) {
                $buckets['Same Day'] += $item->count;
            } elseif ($days <= 3) {
                $buckets['1-3 Days'] += $item->count;
            } elseif ($days <= 7) {
                $buckets['4-7 Days'] += $item->count;
            } elseif ($days <= 14) {
                $buckets['1-2 Weeks'] += $item->count;
            } elseif ($days <= 28) {
                $buckets['3-4 Weeks'] += $item->count;
            } else {
                $buckets['1+ Month'] += $item->count;
            }
        }

        return collect($buckets)->map(function($count, $label) {
            return ['lead_time_bucket' => $label, 'cancellation_count' => $count];
        });
    }

    /**
     * Get cancellation breakdown by reason
     */
    public function getByReason(Carbon $startDate, Carbon $endDate)
    {
        $teamId = $this->teamId;

        return DB::table('reservations')
            ->where('team_id', $teamId)
            ->where('status', 'cancelled')
            ->whereDate('cancelled_at', '>=', $startDate)
            ->whereDate('cancelled_at', '<=', $endDate)
            ->select(
                'cancellation_reason',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total_amount) as total_amount')
            )
            ->groupBy('cancellation_reason')
            ->orderByDesc('count')
            ->get();
    }

    public function getByDayOfWeek(Carbon $startDate, Carbon $endDate)
    {
        $teamId = $this->teamId;

        return DB::table('reservations')
            ->where('team_id', $teamId)
            ->where('status', 'cancelled')
            ->whereDate('cancelled_at', '>=', $startDate)
            ->whereDate('cancelled_at', '<=', $endDate)
            ->select(
                DB::raw('DAYNAME(cancelled_at) as day_name'),
                DB::raw('DAYOFWEEK(cancelled_at) as day_number'),
                DB::raw('COUNT(*) as cancellation_count')
            )
            ->groupBy('day_name', 'day_number')
            ->orderBy('day_number')
            ->get();
    }

    public function getBySeason(Carbon $startDate, Carbon $endDate)
    {
        $teamId = $this->teamId;

        return DB::table('reservations')
            ->where('team_id', $teamId)
            ->where('status', 'cancelled')
            ->whereDate('cancelled_at', '>=', $startDate)
            ->whereDate('cancelled_at', '<=', $endDate)
            ->select(
                DB::raw('MONTHNAME(cancelled_at) as month'),
                DB::raw('MONTH(cancelled_at) as month_number'),
                DB::raw('COUNT(*) as cancellation_count'),
                DB::raw('SUM(total_amount) as total_amount')
            )
            ->groupBy('month_number', 'month')
            ->orderBy('month_number')
            ->get();
    }

    /**
     * Get cancellation pattern by day of week
     */
    public function getByDayOfWeek(Carbon $startDate, Carbon $endDate)
    {
        $teamId = $this->teamId;

        return DB::table('reservations')
            ->where('team_id', $teamId)
            ->where('status', 'cancelled')
            ->whereDate('updated_at', '>=', $startDate)
            ->whereDate('updated_at', '<=', $endDate)
            ->select(
                DB::raw('DAYNAME(updated_at) as day_name'),
                DB::raw('DAYOFWEEK(updated_at) as day_number'),
                DB::raw('COUNT(*) as cancellation_count')
            )
            ->groupBy('day_name', 'day_number')
            ->orderBy('day_number')
            ->get();
    }

    /**
     * Get cancellation pattern by season/month
     */
    public function getBySeason(Carbon $startDate, Carbon $endDate)
    {
        $teamId = $this->teamId;

        return DB::table('reservations')
            ->where('team_id', $teamId)
            ->where('status', 'cancelled')
            ->whereDate('updated_at', '>=', $startDate)
            ->whereDate('updated_at', '<=', $endDate)
            ->select(
                DB::raw('MONTHNAME(updated_at) as month'),
                DB::raw('MONTH(updated_at) as month_number'),
                DB::raw('COUNT(*) as cancellation_count'),
                DB::raw('SUM(total_amount) as total_amount')
            )
            ->groupBy('month_number', 'month')
            ->orderBy('month_number')
            ->get();
    }
}
