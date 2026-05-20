<?php

namespace App\Repositories;

use App\Models\Team;
use App\Models\Unit;
use App\Models\Reservation;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

class DashboardRepository
{
    public function getTodayArrivals(Team $team)
    {
        return Reservation::where('team_id', $team->id)
            ->whereDate('check_in', Carbon::today())
            ->count();
    }

    public function getTodayDepartures(Team $team)
    {
        return Reservation::where('team_id', $team->id)
            ->whereDate('check_out', Carbon::today())
            ->count();
    }

    public function getInHouseCount(Team $team)
    {
        return Reservation::where('team_id', $team->id)
            ->where('status', 'checked_in')
            ->count();
    }

    public function getHousekeepingSummary(Team $team)
    {
        return Unit::where('team_id', $team->id)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();
    }

    public function getPendingTasks(Team $team)
    {
        // Placeholder for tasks module if it exists, otherwise 0
        return 0;
    }

    public function getAlerts(Team $team)
    {
        $alerts = [];

        // Pending check-ins
        $pendingCheckins = Reservation::where('team_id', $team->id)
            ->whereDate('check_in', Carbon::today())
            ->where('status', 'confirmed')
            ->count();
        if ($pendingCheckins > 0) {
            $alerts[] = ['type' => 'warning', 'message' => "$pendingCheckins pending check-ins for today"];
        }

        // Pending check-outs
        $pendingCheckouts = Reservation::where('team_id', $team->id)
            ->whereDate('check_out', Carbon::today())
            ->where('status', 'checked_in')
            ->count();
        if ($pendingCheckouts > 0) {
            $alerts[] = ['type' => 'warning', 'message' => "$pendingCheckouts pending check-outs for today"];
        }

        // Unpaid balances (simplified)
        $unpaidCount = Reservation::where('team_id', $team->id)
            ->where('status', 'checked_in')
            ->where('total_price', '>', function($query) {
                $query->select(DB::raw('SUM(amount)'))
                    ->from('transactions')
                    ->whereColumn('transactions.reservation_id', 'reservations.id');
            })
            ->count();
        if ($unpaidCount > 0) {
            $alerts[] = ['type' => 'danger', 'message' => "$unpaidCount in-house guests with unpaid balances"];
        }

        return $alerts;
    }

    public function getTotalUnits(Team $team)
    {
        return Unit::where('team_id', $team->id)->count();
    }

    public function getOccupiedUnits(Team $team, Carbon $date)
    {
        return Reservation::where('team_id', $team->id)
            ->whereDate('check_in', '<=', $date)
            ->whereDate('check_out', '>', $date)
            ->whereIn('status', ['checked_in', 'confirmed'])
            ->count();
    }

    public function getOccupancyTrend(Team $team, $days)
    {
        $trend = [];
        $totalUnits = $this->getTotalUnits($team);

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $occupied = $this->getOccupiedUnits($team, $date);
            $rate = $totalUnits > 0 ? round(($occupied / $totalUnits) * 100, 2) : 0;
            
            $trend[] = [
                'date' => $date->format('Y-m-d'),
                'rate' => $rate
            ];
        }

        return $trend;
    }

    public function getRevenueForDate(Team $team, Carbon $date)
    {
        return Transaction::where('team_id', $team->id)
            ->whereDate('created_at', $date)
            ->where('kind', 'payment') // Assuming 'payment' is the kind for revenue
            ->sum('amount');
    }

    public function getRevenueForPeriod(Team $team, Carbon $start, Carbon $end)
    {
        return Transaction::where('team_id', $team->id)
            ->whereBetween('created_at', [$start->startOfDay(), $end->endOfDay()])
            ->where('kind', 'payment')
            ->sum('amount');
    }

    public function getRevenueTrend(Team $team, $days)
    {
        $trend = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $revenue = $this->getRevenueForDate($team, $date);
            
            $trend[] = [
                'date' => $date->format('Y-m-d'),
                'revenue' => (float)$revenue
            ];
        }

        return $trend;
    }

    public function getPendingCheckins(Team $team)
    {
        return Reservation::where('team_id', $team->id)
            ->whereDate('check_in', Carbon::today())
            ->whereIn('status', ['confirmed', 'booked'])
            ->with(['guest', 'unit'])
            ->get();
    }

    public function getPendingCheckouts(Team $team)
    {
        return Reservation::where('team_id', $team->id)
            ->whereDate('check_out', Carbon::today())
            ->where('status', 'checked_in')
            ->with(['guest', 'unit'])
            ->get();
    }

    public function getNoShowCandidates(Team $team)
    {
        return Reservation::where('team_id', $team->id)
            ->whereDate('check_in', '<=', Carbon::today())
            ->whereIn('status', ['confirmed', 'booked'])
            ->with(['guest', 'unit'])
            ->get();
    }

    public function getUnpaidBalances(Team $team)
    {
        return Reservation::where('team_id', $team->id)
            ->where('status', 'checked_in')
            ->whereRaw('(total_price - COALESCE((SELECT SUM(amount) FROM transactions WHERE transactions.payable_id = reservations.id AND transactions.payable_type = ? AND transactions.kind = "payment"), 0)) > 0', ['App\Models\Reservation'])
            ->with(['guest', 'unit'])
            ->get();
    }

    public function getWalkInsToday(Team $team)
    {
        return Reservation::where('team_id', $team->id)
            ->whereDate('created_at', Carbon::today())
            ->whereDate('check_in', Carbon::today())
            ->count();
    }

    public function getVipArrivals(Team $team)
    {
        // TODO: Add is_vip column to guests table
        return 0;
    }

    public function getUnitsByStatus(Team $team, $status)
    {
        return Unit::where('team_id', $team->id)
            ->where('status', $status)
            ->count();
    }

    public function getPriorityCleaningTasks(Team $team)
    {
        return DB::table('unit_cleanings')
            ->where('team_id', $team->id)
            ->whereNull('completed_at')
            ->count();
    }

    public function getOverdueCleaningTasks(Team $team)
    {
        return DB::table('unit_cleanings')
            ->join('units', 'unit_cleanings.unit_id', '=', 'units.id')
            ->where('unit_cleanings.team_id', $team->id)
            ->whereNull('unit_cleanings.completed_at')
            ->where('unit_cleanings.start_at', '<', Carbon::now())
            ->select('unit_cleanings.*', 'units.number as unit_number', 'units.status as unit_status')
            ->orderBy('unit_cleanings.start_at')
            ->get();
    }

    public function getDetailedHousekeeping(Team $team)
    {
        return [
            'metrics' => [
                'clean' => $this->getUnitsByStatus($team, 'clean'),
                'dirty' => $this->getUnitsByStatus($team, 'dirty'),
                'cleaning' => $this->getUnitsByStatus($team, 'cleaning'),
                'inspection' => $this->getUnitsByStatus($team, 'inspection'),
                'maintenance' => $this->getUnitsByStatus($team, 'maintenance'),
            ],
            'overdue_tasks' => $this->getOverdueCleaningTasks($team),
            'floor_plan' => $this->getFloorWiseStatus($team)
        ];
    }

    public function getCashBalance(Team $team)
    {
        return Transaction::where('team_id', $team->id)
            ->sum('amount'); // This is very simplified
    }

    public function getPendingInvoices(Team $team)
    {
        return DB::table('invoices')
            ->where('team_id', $team->id)
            ->where('status', 'pending')
            ->count();
    }

    public function getOverduePayments(Team $team)
    {
        return DB::table('invoices')
            ->where('team_id', $team->id)
            ->where('status', 'overdue')
            ->count();
    }

    public function getLastNightAuditRun(Team $team)
    {
        return DB::table('night_audit_log')
            ->where('team_id', $team->id)
            ->latest('started_at')
            ->first();
    }

    public function getPendingNightAudits(Team $team)
    {
        return DB::table('night_audit_log')
            ->where('team_id', $team->id)
            ->where('status', 'pending')
            ->count();
    }

    public function getCurrentBusinessDate(Team $team)
    {
        return $team->business_date ?: Carbon::today()->format('Y-m-d');
    }

    public function getNightAuditIssues(Team $team)
    {
        $lastRun = $this->getLastNightAuditRun($team);
        if ($lastRun && $lastRun->status === 'failed') {
            return $lastRun->steps_failed ? json_decode($lastRun->steps_failed, true) : ['error' => 'Unknown failure steps'];
        }
        return [];
    }

    public function getIntegrationStatus(Team $team, $integrationName)
    {
        $integration = DB::table('integrations')->where('name', $integrationName)->first();
        if (!$integration) return null;

        $setting = DB::table('integration_settings')
            ->where('team_id', $team->id)
            ->where('integration_id', $integration->id)
            ->first();

        $failedSyncs = DB::table('integration_logs')
            ->where('team_id', $team->id)
            ->where('integration_id', $integration->id)
            ->where('response_code', '>=', 400)
            ->count();

        return [
            'id' => $integration->id,
            'name' => $integration->name,
            'is_enabled' => $setting ? (bool)$setting->is_enabled : false,
            'status' => $setting && $setting->is_enabled ? ($setting->sync_status ?? 'connected') : 'disconnected',
            'last_sync_at' => $setting ? $setting->last_sync_at : null,
            'failed_syncs' => $failedSyncs,
        ];
    }

    public function getRecentIntegrationErrors(Team $team, $limit = 10)
    {
        return DB::table('integration_logs')
            ->join('integrations', 'integration_logs.integration_id', '=', 'integrations.id')
            ->where('integration_logs.team_id', $team->id)
            ->where('integration_logs.response_code', '>=', 400)
            ->select('integration_logs.*', 'integrations.name as integration_name')
            ->orderByDesc('integration_logs.created_at')
            ->limit($limit)
            ->get();
    }

    public function getRecentActivity(Team $team, $limit = 20)
    {
        return Activity::where('properties->team_id', $team->id)
            ->orWhere(function($query) use ($team) {
                $query->whereHas('causer', function($q) use ($team) {
                    $q->whereHas('teams', function($qt) use ($team) {
                        $qt->where('teams.id', $team->id);
                    });
                });
            })
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function getOccupancyByCategory(Team $team)
    {
        return DB::table('units')
            ->join('unit_categories', 'units.unit_category_id', '=', 'unit_categories.id')
            ->where('units.team_id', $team->id)
            ->select('unit_categories.name', DB::raw('count(*) as total'), 
                DB::raw('count(case when units.status in ("checked_in", "booked") then 1 end) as occupied'))
            ->groupBy('unit_categories.id', 'unit_categories.name')
            ->get()
            ->map(function($item) {
                $item->occupancy_rate = $item->total > 0 ? round(($item->occupied / $item->total) * 100, 2) : 0;
                return $item;
            });
    }

    public function getFloorWiseStatus(Team $team)
    {
        return Unit::where('team_id', $team->id)
            ->with('unitCategory')
            ->orderBy('floor')
            ->orderBy('unit_number')
            ->get()
            ->groupBy('floor');
    }

    public function getDetailedOccupancy(Team $team)
    {
        $today = Carbon::today();
        $totalUnits = $this->getTotalUnits($team);
        
        return [
            'metrics' => [
                'total' => $totalUnits,
                'occupied' => $this->getOccupiedUnits($team, $today),
                'available' => $this->getUnitsByStatus($team, 'clean'),
                'dirty' => $this->getUnitsByStatus($team, 'dirty'),
                'maintenance' => $this->getUnitsByStatus($team, 'maintenance'),
            ],
            'by_category' => $this->getOccupancyByCategory($team),
            'trend' => $this->getOccupancyTrend($team, 30),
            'floor_plan' => $this->getFloorWiseStatus($team)
        ];
    }

    public function getDetailedRevenue(Team $team)
    {
        $today = Carbon::today();
        
        $todayRevenue = $this->getRevenueForDate($team, $today);
        $mtdRevenue = $this->getRevenueForPeriod($team, $today->copy()->startOfMonth(), $today);
        $ytdRevenue = $this->getRevenueForPeriod($team, $today->copy()->startOfYear(), $today);

        $totalUnits = $this->getTotalUnits($team);
        $occupiedToday = $this->getOccupiedUnits($team, $today);
        
        // ADR & RevPAR (Simple today calculation)
        $adr = $occupiedToday > 0 ? $todayRevenue / $occupiedToday : 0;
        $revpar = $totalUnits > 0 ? $todayRevenue / $totalUnits : 0;

        return [
            'metrics' => [
                'today' => $todayRevenue,
                'mtd' => $mtdRevenue,
                'ytd' => $ytdRevenue,
                'adr' => round($adr, 2),
                'revpar' => round($revpar, 2),
            ],
            'by_source' => $this->getRevenueBySource($team),
            'trend' => $this->getRevenueTrend($team, 30),
            'top_units' => $this->getTopRevenueUnits($team, 10)
        ];
    }

    public function getRevenueBySource(Team $team)
    {
        return DB::table('transactions')
            ->join('reservations', function($join) {
                $join->on('transactions.payable_id', '=', 'reservations.id')
                    ->where('transactions.payable_type', '=', 'App\Models\Reservation');
            })
            ->leftJoin('sources', 'reservations.source_id', '=', 'sources.id')
            ->where('transactions.team_id', $team->id)
            ->where('transactions.kind', 'payment')
            ->whereNotNull('reservations.source_id')
            ->select('sources.name', DB::raw('SUM(transactions.amount) as total'))
            ->groupBy('sources.id', 'sources.name')
            ->get();
    }

    public function getTopRevenueUnits(Team $team, $limit = 10)
    {
        return DB::table('transactions')
            ->join('reservations', function($join) {
                $join->on('transactions.payable_id', '=', 'reservations.id')
                    ->where('transactions.payable_type', '=', 'App\Models\Reservation');
            })
            ->join('units', 'reservations.unit_id', '=', 'units.id')
            ->where('transactions.team_id', $team->id)
            ->where('transactions.kind', 'payment')
            ->select('units.unit_number as number', DB::raw('SUM(transactions.amount) as revenue'))
            ->groupBy('units.id', 'units.unit_number')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get();
    }
}
