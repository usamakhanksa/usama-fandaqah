<?php

namespace App\Services\Dashboard;

use App\Models\Team;
use App\Repositories\DashboardRepository;
use Carbon\Carbon;

class DashboardService
{
    protected DashboardRepository $repository;

    public function __construct(DashboardRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getDashboardData(Team $team): array
    {
        return [
            'occupancy' => $this->getOccupancyData($team),
            'revenue' => $this->getRevenueData($team),
            'arrivals' => $this->repository->getTodayArrivals($team),
            'departures' => $this->repository->getTodayDepartures($team),
            'inHouse' => $this->repository->getInHouseCount($team),
            'housekeeping' => $this->repository->getHousekeepingSummary($team),
            'pendingTasks' => $this->repository->getPendingTasks($team),
            'alerts' => $this->repository->getAlerts($team),
            'recentActivity' => $this->repository->getRecentActivity($team, 20),
        ];
    }

    public function getOccupancyData(Team $team): array
    {
        $today = Carbon::today();
        $totalUnits = $this->repository->getTotalUnits($team);
        $occupiedUnits = $this->repository->getOccupiedUnits($team, $today);
        
        $occupancyRate = $totalUnits > 0 
            ? round(($occupiedUnits / $totalUnits) * 100, 2) 
            : 0;

        return [
            'total_units' => $totalUnits,
            'occupied' => $occupiedUnits,
            'available' => $totalUnits - $occupiedUnits,
            'occupancy_rate' => $occupancyRate,
            'trend' => $this->repository->getOccupancyTrend($team, 7),
        ];
    }

    public function getRevenueData(Team $team): array
    {
        $today = Carbon::today();
        
        return [
            'today' => $this->repository->getRevenueForDate($team, $today),
            'this_week' => $this->repository->getRevenueForPeriod($team, $today->copy()->startOfWeek(), $today),
            'this_month' => $this->repository->getRevenueForPeriod($team, $today->copy()->startOfMonth(), $today),
            'trend' => $this->repository->getRevenueTrend($team, 7),
        ];
    }

    public function getFrontDeskData(Team $team): array
    {
        return [
            'pending_checkins' => $this->repository->getPendingCheckins($team),
            'pending_checkouts' => $this->repository->getPendingCheckouts($team),
            'walk_ins_today' => $this->repository->getWalkInsToday($team),
            'vip_arrivals' => $this->repository->getVipArrivals($team),
        ];
    }

    public function getHousekeepingData(Team $team): array
    {
        return [
            'clean' => $this->repository->getUnitsByStatus($team, 'clean'),
            'dirty' => $this->repository->getUnitsByStatus($team, 'dirty'),
            'cleaning_in_progress' => $this->repository->getUnitsByStatus($team, 'cleaning'),
            'maintenance' => $this->repository->getUnitsByStatus($team, 'maintenance'),
            'priority_tasks' => $this->repository->getPriorityCleaningTasks($team),
        ];
    }

    public function getFinanceData(Team $team): array
    {
        return [
            'collections_today' => $this->repository->getTodayCollectionsBreakdown($team),
            'open_cashier_shifts' => $this->repository->getOpenCashierShiftsCount($team),
            'promissory_outstanding' => $this->repository->getPromissoryOutstanding($team),
            'invoices_pending_zatca' => $this->repository->getZatcaPendingCount($team),
            'credit_notes_today' => $this->repository->getCreditNotesTodayCount($team),
            'payment_method_breakdown' => $this->repository->getPaymentMethodBreakdown($team),
        ];
    }

    public function getNightAuditData(Team $team): array
    {
        return [
            'last_run' => $this->repository->getLastNightAuditRun($team),
            'pending_runs' => $this->repository->getPendingNightAudits($team),
            'business_date' => $this->repository->getCurrentBusinessDate($team),
            'needs_attention' => $this->repository->getNightAuditIssues($team),
            'next_scheduled_run' => $team->night_audit_auto_run_time,
            'auto_enabled' => $team->night_audit_auto_enabled,
        ];
    }

    public function getIntegrationHealthData(Team $team): array
    {
        $integrations = ['ZATCA', 'Qoyod', 'STAAH', 'Jawaly', 'IPTV', 'Let Link'];
        $statuses = [];
        foreach ($integrations as $name) {
            $status = $this->repository->getIntegrationStatus($team, $name);
            if ($status) {
                $statuses[] = $status;
            }
        }

        return [
            'integrations' => $statuses,
            'recent_errors' => $this->repository->getRecentIntegrationErrors($team),
        ];
    }
}
