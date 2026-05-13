<?php

namespace App\Services\Reports;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Reservation;
use App\Models\Unit;
use App\Models\UnitStatus;
use App\Models\CheckInRecord;
use App\Models\CheckOutRecord;
use App\UnitMaintenance;

class HousekeepingDiscrepancyReportService extends ReportService
{
    /**
     * Get housekeeping discrepancy summary
     */
    public function getDiscrepancySummary(Carbon $startDate, Carbon $endDate)
    {
        $teamId = $this->teamId;

        // Rooms with status mismatch: front desk status vs housekeeping status
        $mismatchedRooms = $this->getStatusMismatch($teamId);

        // Rooms not cleaned after checkout
        $notCleanedAfterCheckout = $this->getNotCleanedAfterCheckout($startDate, $endDate, $teamId);

        // Maintenance issues blocking room sales
        $maintenanceBlocks = $this->getMaintenanceBlocks($teamId);

        // Average cleaning time
        $avgCleaningTime = $this->getAverageCleaningTime($startDate, $endDate, $teamId);

        return [
            'mismatched_rooms' => $mismatchedRooms->count(),
            'not_cleaned_after_checkout' => $notCleanedAfterCheckout->count(),
            'maintenance_blocks' => $maintenanceBlocks->count(),
            'avg_cleaning_time_minutes' => $avgCleaningTime,
            'mismatch_details' => $mismatchedRooms->take(50),
            'uncleaned_details' => $notCleanedAfterCheckout->take(50),
            'maintenance_details' => $maintenanceBlocks->take(50),
        ];
    }

    /**
     * Get rooms with status mismatch (front desk vs housekeeping)
     */
    public function getStatusMismatch($teamId)
    {
        // Front desk room status (from units.status - typically room status like 'occupied', 'vacant', 'dirty')
        // Housekeeping status (from unit_status_id)
        // We look for inconsistencies

        return DB::table('units')
            ->join('unit_statuses', 'units.unit_status_id', '=', 'unit_statuses.id')
            ->where('units.team_id', $teamId)
            ->where(function($q) {
                $q->where(function($sub) {
                    $sub->where('units.status', 'occupied')
                        ->whereNotIn('unit_statuses.name', ['occupied', 'clean']);
                })
                ->orWhere(function($sub) {
                    $sub->where('units.status', 'vacant')
                        ->whereIn('unit_statuses.name', ['occupied', 'dirty', 'cleaning']);
                });
            })
            ->select(
                'units.id',
                'units.name as room_number',
                'units.status as front_desk_status',
                'unit_statuses.name as housekeeping_status'
            )
            ->limit(100)
            ->get();
    }

    /**
     * Get rooms not cleaned after checkout
     */
    public function getNotCleanedAfterCheckout(Carbon $startDate, Carbon $endDate, $teamId)
    {
        // Find checkouts where the room was not cleaned within expected timeframe (e.g., 4 hours)
        $thresholdHours = 4;

        return DB::table('check_out_records')
            ->join('units', 'check_out_records.unit_id', '=', 'units.id')
            ->join('unit_statuses', 'units.unit_status_id', '=', 'unit_statuses.id')
            ->where('check_out_records.team_id', $teamId)
            ->whereDate('check_out_records.created_at', '>=', $startDate)
            ->whereDate('check_out_records.created_at', '<=', $endDate)
            ->whereNotIn('unit_statuses.name', ['clean', 'vacant'])
            ->where(function($q) use ($thresholdHours) {
                $q->whereNull('units.updated_at')
                  ->orWhereRaw("TIMESTAMPDIFF(HOUR, check_out_records.created_at, units.updated_at) > {$thresholdHours}");
            })
            ->select(
                'units.name as room_number',
                'check_out_records.created_at as checkout_time',
                'units.updated_at as last_updated',
                DB::raw("TIMESTAMPDIFF(MINUTE, check_out_records.created_at, units.updated_at) as minutes_to_clean")
            )
            ->get();
    }

    /**
     * Get maintenance issues blocking room sales
     */
    public function getMaintenanceBlocks($teamId)
    {
        $today = Carbon::today();

        return UnitMaintenance::where('team_id', $teamId)
            ->where(function($q) use ($today) {
                $q->whereNull('completed_at')
                  ->orWhereDate('expected_at', '>=', $today);
            })
            ->whereHas('unit', function($q) use ($teamId) {
                $q->where('team_id', $teamId)
                  ->where('is_active', true);
            })
            ->with(['unit' => function($q) {
                $q->select('id', 'name', 'unit_number', 'room_id');
            }])
            ->orderBy('expected_at', 'asc')
            ->limit(100)
            ->get();
    }

    /**
     * Get average cleaning time
     */
    public function getAverageCleaningTime(Carbon $startDate, Carbon $endDate, $teamId)
    {
        // Calculate average time between check-out and room status change to 'clean'
        $records = DB::table('check_out_records')
            ->join('units', 'check_out_records.unit_id', '=', 'units.id')
            ->where('check_out_records.team_id', $teamId)
            ->whereDate('check_out_records.created_at', '>=', $startDate)
            ->whereDate('check_out_records.created_at', '<=', $endDate)
            ->whereNotNull('units.updated_at')
            ->select(
                DB::raw('TIMESTAMPDIFF(MINUTE, check_out_records.created_at, units.updated_at) as cleaning_minutes')
            )
            ->get();

        if ($records->isEmpty()) {
            return 0;
        }

        return round($records->avg('cleaning_minutes'), 0);
    }

    /**
     * Get daily cleaning time trend
     */
    public function getDailyCleaningTimeTrend(Carbon $startDate, Carbon $endDate, $teamId)
    {
        $current = $startDate->copy();
        $trend = [];

        while ($current->lte($endDate)) {
            $dailyRecords = DB::table('check_out_records')
                ->join('units', 'check_out_records.unit_id', '=', 'units.id')
                ->where('check_out_records.team_id', $teamId)
                ->whereDate('check_out_records.created_at', $current->toDateString())
                ->whereNotNull('units.updated_at')
                ->select(DB::raw('AVG(TIMESTAMPDIFF(MINUTE, check_out_records.created_at, units.updated_at)) as avg_minutes'))
                ->value('avg_minutes');

            $trend[] = [
                'date' => $current->toDateString(),
                'avg_cleaning_minutes' => round($dailyRecords ?? 0, 0),
            ];

            $current->addDay();
        }

        return $trend;
    }
}
