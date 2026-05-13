<?php

namespace App\Services\Reports;

use App\Models\Transaction;
use App\Models\Reservation;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RevenueCalculationService extends ReportService
{
    /**
     * Get revenue summary for a date range
     */
    public function getRevenueSummary(Carbon $startDate, Carbon $endDate)
    {
        $transactions = Transaction::where('team_id', $this->teamId)
            ->where('kind', 'payment')
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->get();

        $totalRevenue = $transactions->sum('amount');
        
        // Breakdown by category
        $roomRevenue = Transaction::where('team_id', $this->teamId)
            ->where('kind', 'payment')
            ->where(function($q) {
                $q->where('payable_type', Reservation::class)
                  ->orWhere('meta->category', 'reservation');
            })
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->sum('amount');

        $fbRevenue = Transaction::where('team_id', $this->teamId)
            ->where('kind', 'payment')
            ->where('meta->category', 'service')
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->sum('amount');
        
        $otherRevenue = $totalRevenue - $roomRevenue - $fbRevenue;

        // Occupancy for ADR/RevPAR
        $totalUnits = Unit::where('team_id', $this->teamId)->count();
        $occupiedDays = Reservation::where('team_id', $this->teamId)
            ->where('status', 'checked-in')
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('check_in', [$startDate, $endDate])
                    ->orWhereBetween('check_out', [$startDate, $endDate]);
            })
            ->count(); // This is a simplification

        $adr = $occupiedDays > 0 ? $roomRevenue / $occupiedDays : 0;
        $revpar = $totalUnits > 0 ? $roomRevenue / ($totalUnits * $startDate->diffInDays($endDate) ?: 1) : 0;

        return [
            'total_revenue' => round($totalRevenue, 2),
            'room_revenue' => round($roomRevenue, 2),
            'fb_revenue' => round($fbRevenue, 2),
            'other_revenue' => round($otherRevenue, 2),
            'adr' => round($adr, 2),
            'revpar' => round($revpar, 2),
            'goppar' => 0, // Placeholder
        ];
    }

    /**
     * Get revenue by source
     */
    public function getRevenueBySource(Carbon $startDate, Carbon $endDate)
    {
        return DB::table('transactions')
            ->join('reservations', function($join) {
                $join->on('transactions.payable_id', '=', 'reservations.id')
                    ->where('transactions.payable_type', '=', Reservation::class);
            })
            ->join('sources', 'reservations.source_id', '=', 'sources.id')
            ->where('transactions.team_id', $this->teamId)
            ->where('transactions.kind', 'payment')
            ->whereBetween('transactions.created_at', [$startDate, $endDate])
            ->select('sources.name', DB::raw('SUM(transactions.amount) as total'))
            ->groupBy('sources.id', 'sources.name')
            ->get();
    }

    /**
     * Get revenue by market segment
     */
    public function getRevenueByMarketSegment(Carbon $startDate, Carbon $endDate)
    {
        // This assumes a 'market_segment' column in reservations or similar
        return DB::table('transactions')
            ->join('reservations', function($join) {
                $join->on('transactions.payable_id', '=', 'reservations.id')
                    ->where('transactions.payable_type', '=', Reservation::class);
            })
            ->where('transactions.team_id', $this->teamId)
            ->where('transactions.kind', 'payment')
            ->whereBetween('transactions.created_at', [$startDate, $endDate])
            ->select('reservations.reservation_category_type as segment', DB::raw('SUM(transactions.amount) as total'))
            ->groupBy('reservations.reservation_category_type')
            ->get();
    }

    /**
     * Get daily revenue trend
     */
    public function getDailyRevenueTrend(Carbon $startDate, Carbon $endDate)
    {
        $trend = [];
        $current = $startDate->copy();

        while ($current->lte($endDate)) {
            $revenue = Transaction::where('team_id', $this->teamId)
                ->where('kind', 'payment')
                ->whereDate('created_at', $current)
                ->sum('amount');

            $trend[] = [
                'date' => $current->toDateString(),
                'revenue' => (float)$revenue
            ];
            $current->addDay();
        }

        return $trend;
    }
}
