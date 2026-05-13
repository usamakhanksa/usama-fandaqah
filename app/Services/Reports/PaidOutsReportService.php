<?php

namespace App\Services\Reports;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\PaidOut;
use App\Models\User;

class PaidOutsReportService extends ReportService
{
    /**
     * Get paid-outs summary by date range
     */
    public function getPaidOutsSummary(Carbon $startDate, Carbon $endDate)
    {
        $teamId = $this->teamId;

        $paidOuts = PaidOut::where('team_id', $teamId)
            ->whereDate('paid_out_date', '>=', $startDate)
            ->whereDate('paid_out_date', '<=', $endDate)
            ->with(['creator', 'cashierShift'])
            ->get();

        $totalPaidOuts = $paidOuts->sum('amount');
        $avgPaidOut = $paidOuts->avg('amount') ?: 0;
        $count = $paidOuts->count();

        // Get revenue for the same period for comparison
        $revenue = DB::table('transactions')
            ->where('team_id', $teamId)
            ->where('kind', 'payment')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->sum('amount');

        $paidOutToRevenueRatio = $revenue > 0 ? ($totalPaidOuts / $revenue) * 100 : 0;

        return [
            'total_paid_outs' => $totalPaidOuts,
            'paid_out_count' => $count,
            'average_paid_out' => $avgPaidOut,
            'total_revenue' => $revenue,
            'paid_out_to_revenue_ratio' => round($paidOutToRevenueRatio, 2),
            'paid_outs' => $paidOuts,
        ];
    }

    /**
     * Group paid-outs by category
     */
    public function getByCategory(Carbon $startDate, Carbon $endDate)
    {
        $teamId = $this->teamId;

        return DB::table('paid_outs')
            ->where('team_id', $teamId)
            ->whereDate('paid_out_date', '>=', $startDate)
            ->whereDate('paid_out_date', '<=', $endDate)
            ->select(
                'category',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount) as total_amount')
            )
            ->groupBy('category')
            ->orderByDesc('total_amount')
            ->get();
    }

    /**
     * Group paid-outs by cashier/shift
     */
    public function getByCashier(Carbon $startDate, Carbon $endDate)
    {
        $teamId = $this->teamId;

        return DB::table('paid_outs')
            ->join('users', 'paid_outs.created_by', '=', 'users.id')
            ->where('paid_outs.team_id', $teamId)
            ->whereDate('paid_outs.paid_out_date', '>=', $startDate)
            ->whereDate('paid_outs.paid_out_date', '<=', $endDate)
            ->select(
                'users.name as cashier_name',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(paid_outs.amount) as total_amount')
            )
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_amount')
            ->get();
    }

    /**
     * Get daily paid-outs trend
     */
    public function getDailyTrend(Carbon $startDate, Carbon $endDate)
    {
        $teamId = $this->teamId;

        return DB::table('paid_outs')
            ->where('team_id', $teamId)
            ->whereDate('paid_out_date', '>=', $startDate)
            ->whereDate('paid_out_date', '<=', $endDate)
            ->select(
                DB::raw('DATE(paid_out_date) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount) as total_amount')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Get paid-outs by status (pending, approved, rejected)
     */
    public function getByStatus(Carbon $startDate, Carbon $endDate)
    {
        $teamId = $this->teamId;

        return DB::table('paid_outs')
            ->where('team_id', $teamId)
            ->whereDate('paid_out_date', '>=', $startDate)
            ->whereDate('paid_out_date', '<=', $endDate)
            ->select(
                'status',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount) as total_amount')
            )
            ->groupBy('status')
            ->orderByDesc('total_amount')
            ->get();
    }
}
