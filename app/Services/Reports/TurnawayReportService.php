<?php

namespace App\Services\Reports;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\TurnawayLog;
use App\Models\TurnawayReason;
use App\Models\Unit;

class TurnawayReportService extends ReportService
{
    /**
     * Get turnaway summary by date range
     */
    public function getTurnawaySummary(Carbon $startDate, Carbon $endDate)
    {
        $teamId = $this->teamId;

        $turnaways = TurnawayLog::where('team_id', $teamId)
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->with(['reason', 'createdBy'])
            ->get();

        $totalTurnaways = $turnaways->count();

        // Estimate revenue loss: average room rate * number of turnaways
        $avgRoomRate = $this->getAverageRoomRate($teamId);
        $estimatedRevenueLoss = $totalTurnaways * $avgRoomRate;

        return [
            'total_turnaways' => $totalTurnaways,
            'estimated_revenue_loss' => $estimatedRevenueLoss,
            'average_room_rate' => $avgRoomRate,
            'turnaways' => $turnaways,
        ];
    }

    /**
     * Get turnaways by reason
     */
    public function getByReason(Carbon $startDate, Carbon $endDate)
    {
        $teamId = $this->teamId;

        return DB::table('turnaway_logs')
            ->join('turnaway_reasons', 'turnaway_logs.reason_id', '=', 'turnaway_reasons.id')
            ->where('turnaway_logs.team_id', $teamId)
            ->whereDate('turnaway_logs.date', '>=', $startDate)
            ->whereDate('turnaway_logs.date', '<=', $endDate)
            ->select(
                'turnaway_reasons.name as reason',
                DB::raw('COUNT(*) as turnaway_count'),
                DB::raw('SUM(IFNULL(turnaway_logs.estimated_value, 0)) as estimated_loss')
            )
            ->groupBy('turnaway_reasons.id', 'turnaway_reasons.name')
            ->orderByDesc('turnaway_count')
            ->get();
    }

    /**
     * Get turnaways by room type requested
     */
    public function getByRoomType(Carbon $startDate, Carbon $endDate)
    {
        $teamId = $this->teamId;

        return DB::table('turnaway_logs')
            ->where('team_id', $teamId)
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->select(
                'room_type_requested',
                DB::raw('COUNT(*) as turnaway_count'),
                DB::raw('SUM(IFNULL(estimated_value, 0)) as estimated_loss')
            )
            ->groupBy('room_type_requested')
            ->orderByDesc('turnaway_count')
            ->get();
    }

    /**
     * Get turnaways by date (daily trend)
     */
    public function getDailyTrend(Carbon $startDate, Carbon $endDate)
    {
        $teamId = $this->teamId;

        return DB::table('turnaway_logs')
            ->where('team_id', $teamId)
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->select(
                DB::raw('DATE(date) as date'),
                DB::raw('COUNT(*) as turnaway_count'),
                DB::raw('SUM(IFNULL(estimated_value, 0)) as estimated_loss')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Calculate average room rate for revenue loss estimation
     */
    private function getAverageRoomRate($teamId)
    {
        // Use average of room base prices or past reservation rates
        $avgRoomPrice = DB::table('rooms')
            ->join('room_types', 'rooms.room_type_id', '=', 'room_types.id')
            ->where('rooms.team_id', $teamId)
            ->avg('room_types.base_price');

        return $avgRoomPrice ? round($avgRoomPrice, 2) : 500; // default 500 if no data
    }
}
