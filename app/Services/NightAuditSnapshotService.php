<?php

namespace App\Services;

use App\Team;
use App\Models\NightAuditOccupancySnapshot;
use Illuminate\Support\Facades\DB;

class NightAuditSnapshotService
{
    public function createSnapshot(Team $team, $runNumber = 1, $date = null)
    {
        $businessDate = $date ?: $team->business_date;

        // --- Occupancy Calculations ---
        $totalRooms = DB::table('units')->where('team_id', $team->id)->count() ?: 1;
        $occupiedRooms = DB::table('reservations')
            ->where('team_id', $team->id)
            ->where('status', 'checked_in')
            ->count();
        
        $cleaningRooms = DB::table('units')->where('team_id', $team->id)->where('status', 'cleaning')->count();
        $maintRooms = DB::table('units')->where('team_id', $team->id)->where('status', 'maintenance')->count();
        
        $arrivals = DB::table('reservations')
            ->where('team_id', $team->id)
            ->whereDate('check_in', $businessDate)
            ->count();
            
        $departures = DB::table('reservations')
            ->where('team_id', $team->id)
            ->whereDate('check_out', $businessDate)
            ->count();

        // --- Revenue Calculations (from frozen transactions for this business date) ---
        $roomRevenue = DB::table('transactions')
            ->join('business_date_transactions', 'transactions.id', '=', 'business_date_transactions.transaction_id')
            ->where('business_date_transactions.team_id', $team->id)
            ->where('business_date_transactions.business_date', $businessDate)
            ->where('transactions.type', 'withdraw')
            // Add filter for room revenue category if available
            ->sum('amount') / 100;

        $occupancyPct = ($occupiedRooms / $totalRooms) * 100;
        $adr = $occupiedRooms > 0 ? $roomRevenue / $occupiedRooms : 0;
        $revpar = $totalRooms > 0 ? $roomRevenue / $totalRooms : 0;

        $snapshot = NightAuditOccupancySnapshot::create([
            'team_id' => $team->id,
            'business_date' => $businessDate,
            'run_number' => $runNumber,
            'is_final' => true,
            'total_rooms' => $totalRooms,
            'rooms_available' => $totalRooms - $occupiedRooms - $maintRooms,
            'rooms_occupied' => $occupiedRooms,
            'rooms_cleaning' => $cleaningRooms,
            'rooms_maintenance' => $maintRooms,
            'occupancy_pct' => $occupancyPct,
            'adr' => $adr,
            'revpar' => $revpar,
            'arrivals_count' => $arrivals,
            'departures_count' => $departures,
            'room_revenue' => $roomRevenue,
            'total_revenue' => $roomRevenue, // Simplified for now
        ]);

        // Add to queue for external syncs/processing
        DB::table('night_audit_snapshot_queue')->insert([
            'snapshot_id' => $snapshot->id,
            'team_id' => $team->id,
            'business_date' => $businessDate,
            'status' => 'pending',
            'queued_at' => now()
        ]);

        return $snapshot;
    }
}
