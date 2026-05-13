<?php

namespace App\Services;

use App\User;
use App\Models\Team;
use App\Repositories\DashboardRepository;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardService
{
    protected $repository;

    public function __construct(DashboardRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get data for the Overview Dashboard
     */
    public function getOverviewData(User $user, $filters)
    {
        $teamId = $filters['teamId'] ?? $user->current_team_id;
        $team = Team::find($teamId);
        
        if (!$team) {
            return [];
        }

        $today = Carbon::today();
        
        // 1. KPIs
        $occupancyData = $this->getOccupancyData($team);
        $revenueData = $this->getRevenueData($team);
        
        return [
            'metrics' => [
                'totalRevenue' => (float) $revenueData['today'],
                'revenueTrend' => 0, // Placeholder
                'occupancyRate' => $occupancyData['occupancy_rate'],
                'occupancyTrend' => 0,
                'availableRooms' => $occupancyData['available'],
                'arrivalsToday' => $this->repository->getTodayArrivals($team),
                'departuresToday' => $this->repository->getTodayDepartures($team),
                'inHouseGuests' => $this->repository->getInHouseCount($team),
                'mtdRevenue' => $revenueData['this_month'],
            ],
            'chart' => [
                'dates' => array_column($revenueData['trend'], 'date'),
                'revenue' => array_column($revenueData['trend'], 'revenue'),
                'occupancy' => array_column($occupancyData['trend'], 'rate'),
            ],
            'rooms' => [
                'occupied' => $occupancyData['occupied'],
                'available' => $occupancyData['available'],
                'maintenance' => $this->repository->getUnitsByStatus($team, 'maintenance'),
            ],
            'alerts' => $this->repository->getAlerts($team),
            'recentActivity' => $this->repository->getRecentActivity($team, 20),
        ];
    }

    public function exportOverviewData(User $user, $filters)
    {
        $data = $this->getOverviewData($user, $filters);
        $metrics = $data['metrics'];

        $filename = "dashboard_overview_" . now()->format('YmdHis') . ".csv";
        $handle = fopen('php://output', 'w');
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // Headers
        fputcsv($handle, ['Metric', 'Value']);
        
        // Data
        fputcsv($handle, ['Occupancy Rate', $metrics['occupancyRate'] . '%']);
        fputcsv($handle, ['Available Rooms', $metrics['availableRooms']]);
        fputcsv($handle, ['Arrivals Today', $metrics['arrivalsToday']]);
        fputcsv($handle, ['Departures Today', $metrics['departuresToday']]);
        fputcsv($handle, ['In-House Guests', $metrics['inHouseGuests']]);
        fputcsv($handle, ['Revenue Today', $metrics['totalRevenue']]);
        fputcsv($handle, ['MTD Revenue', $metrics['mtdRevenue']]);

        fclose($handle);
        exit;
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

    public function getFrontDeskData(User $user, $filters)
    {
        $teamId = $filters['teamId'] ?? $user->current_team_id;
        $team = Team::find($teamId);
        
        if (!$team) return [];

        $unpaidBalances = $this->repository->getUnpaidBalances($team);
        
        $unpaidBalancesList = $unpaidBalances->map(function($res) {
            $paid = \App\Models\Transaction::where('payable_id', $res->id)
                ->where('payable_type', \App\Models\Reservation::class)
                ->where('kind', 'payment')
                ->sum('amount');
            
            $res->balance = $res->total_price - $paid;
            return $res;
        });

        return [
            'metrics' => [
                'arrivals_today' => $this->repository->getTodayArrivals($team),
                'departures_today' => $this->repository->getTodayDepartures($team),
                'in_house' => $this->repository->getInHouseCount($team),
                'walk_ins' => $this->repository->getWalkInsToday($team),
                'vip_arrivals' => $this->repository->getVipArrivals($team),
            ],
            'lists' => [
                'pending_checkins' => $this->repository->getPendingCheckins($team),
                'pending_checkouts' => $this->repository->getPendingCheckouts($team),
                'no_show_candidates' => $this->repository->getNoShowCandidates($team),
                'unpaid_balances' => $unpaidBalancesList,
            ]
        ];
    }

    public function exportFrontDeskData(User $user, $filters)
    {
        $data = $this->getFrontDeskData($user, $filters);
        
        $filename = "front_desk_report_" . now()->format('YmdHis') . ".csv";
        $handle = fopen('php://output', 'w');
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // Metrics Header
        fputcsv($handle, ['Metric', 'Value']);
        fputcsv($handle, ['Arrivals Today', $data['metrics']['arrivals_today']]);
        fputcsv($handle, ['Departures Today', $data['metrics']['departures_today']]);
        fputcsv($handle, ['In-House Guests', $data['metrics']['in_house']]);
        fputcsv($handle, ['Walk-ins', $data['metrics']['walk_ins']]);
        fputcsv($handle, ['VIP Arrivals', $data['metrics']['vip_arrivals']]);
        
        fputcsv($handle, []); // Empty row

        // Pending Check-ins
        fputcsv($handle, ['--- PENDING CHECK-INS ---']);
        fputcsv($handle, ['Reservation Code', 'Guest Name', 'Unit Number']);
        foreach ($data['lists']['pending_checkins'] as $res) {
            $guestName = $res->guest ? trim($res->guest->first_name . ' ' . $res->guest->last_name) : 'N/A';
            $unitNo = $res->unit ? $res->unit->number : 'N/A';
            fputcsv($handle, [$res->code, $guestName, $unitNo]);
        }
        fputcsv($handle, []);

        // Pending Check-outs
        fputcsv($handle, ['--- PENDING CHECK-OUTS ---']);
        fputcsv($handle, ['Reservation Code', 'Guest Name', 'Unit Number']);
        foreach ($data['lists']['pending_checkouts'] as $res) {
            $guestName = $res->guest ? trim($res->guest->first_name . ' ' . $res->guest->last_name) : 'N/A';
            $unitNo = $res->unit ? $res->unit->number : 'N/A';
            fputcsv($handle, [$res->code, $guestName, $unitNo]);
        }
        fputcsv($handle, []);

        // No-show Candidates
        fputcsv($handle, ['--- NO-SHOW CANDIDATES ---']);
        fputcsv($handle, ['Reservation Code', 'Guest Name', 'Check-in Date']);
        foreach ($data['lists']['no_show_candidates'] as $res) {
            $guestName = $res->guest ? trim($res->guest->first_name . ' ' . $res->guest->last_name) : 'N/A';
            fputcsv($handle, [$res->code, $guestName, $res->check_in]);
        }
        fputcsv($handle, []);

        // Unpaid Balances
        fputcsv($handle, ['--- UNPAID BALANCES ---']);
        fputcsv($handle, ['Reservation Code', 'Guest Name', 'Unit Number', 'Balance']);
        foreach ($data['lists']['unpaid_balances'] as $res) {
            $guestName = $res->guest ? trim($res->guest->first_name . ' ' . $res->guest->last_name) : 'N/A';
            $unitNo = $res->unit ? $res->unit->number : 'N/A';
            fputcsv($handle, [$res->code, $guestName, $unitNo, $res->balance]);
        }

        fclose($handle);
        exit;
    }

    /**
     * Get data for the Housekeeping Dashboard
     */
    public function getHousekeepingData(User $user, $filters)
    {
        $teamId = $filters['teamId'] ?? $user->current_team_id;
        $team = Team::find($teamId);

        if (!$team) return [];

        return $this->repository->getDetailedHousekeeping($team);
    }

    public function exportHousekeepingData(User $user, $filters)
    {
        $data = $this->getHousekeepingData($user, $filters);
        
        $filename = "housekeeping_report_" . now()->format('YmdHis') . ".csv";
        $handle = fopen('php://output', 'w');
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // Metrics Header
        fputcsv($handle, ['Metric', 'Value']);
        fputcsv($handle, ['Clean Rooms', $data['metrics']['clean']]);
        fputcsv($handle, ['Dirty Rooms', $data['metrics']['dirty']]);
        fputcsv($handle, ['Cleaning In Progress', $data['metrics']['cleaning']]);
        fputcsv($handle, ['Pending Inspection', $data['metrics']['inspection']]);
        fputcsv($handle, ['Maintenance', $data['metrics']['maintenance']]);
        
        fputcsv($handle, []); // Empty row

        // Overdue Tasks
        fputcsv($handle, ['--- OVERDUE TASKS ---']);
        fputcsv($handle, ['Unit Number', 'Status', 'Start Date', 'Note']);
        foreach ($data['overdue_tasks'] as $task) {
            fputcsv($handle, [$task->unit_number, $task->unit_status, $task->start_at, $task->note]);
        }

        fclose($handle);
        exit;
    }

    /**
     * Get data for the Finance Dashboard
     */
    public function getFinanceData(User $user, $filters)
    {
        $teamId = $filters['teamId'] ?? $user->current_team_id;
        $team = Team::find($teamId);

        return [
            'cash_balance' => $this->repository->getCashBalance($team),
            'pending_invoices' => $this->repository->getPendingInvoices($team),
            'overdue_payments' => $this->repository->getOverduePayments($team),
            'zatca_pending' => $this->repository->getZatcaPendingCount($team),
        ];
    }

    /**
     * Get data for the Night Audit Dashboard
     */
    public function getNightAuditData(User $user, $filters)
    {
        $teamId = $filters['teamId'] ?? $user->current_team_id;
        $team = Team::find($teamId);
        
        return [
            'last_run' => $this->repository->getLastNightAuditRun($team),
            'pending_runs' => $this->repository->getPendingNightAudits($team),
            'business_date' => $this->repository->getCurrentBusinessDate($team),
            'needs_attention' => $this->repository->getNightAuditIssues($team),
        ];
    }

    public function getDetailedOccupancyData(User $user, $filters)
    {
        $teamId = $filters['teamId'] ?? $user->current_team_id;
        $team = Team::find($teamId);
        
        if (!$team) return [];

        return $this->repository->getDetailedOccupancy($team);
    }

    public function getDetailedRevenueData(User $user, $filters)
    {
        $teamId = $filters['teamId'] ?? $user->current_team_id;
        $team = Team::find($teamId);
        
        if (!$team) return [];

        return $this->repository->getDetailedRevenue($team);
    }

    public function exportOccupancyData(User $user, $filters)
    {
        $data = $this->getDetailedOccupancyData($user, $filters);
        $metrics = $data['metrics'];

        $filename = "occupancy_report_" . now()->format('YmdHis') . ".csv";
        $handle = fopen('php://output', 'w');
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // Headers
        fputcsv($handle, ['Category', 'Value']);
        
        // Metrics
        fputcsv($handle, ['Total Units', $metrics['total']]);
        fputcsv($handle, ['Occupied Units', $metrics['occupied']]);
        fputcsv($handle, ['Available Units', $metrics['available']]);
        fputcsv($handle, ['Dirty Units', $metrics['dirty']]);
        fputcsv($handle, ['Maintenance Units', $metrics['maintenance']]);
        
        fputcsv($handle, []); // Empty row
        
        // Category Breakdown
        fputcsv($handle, ['Room Category', 'Occupancy Rate (%)']);
        foreach ($data['by_category'] as $cat) {
            fputcsv($handle, [$cat->name, $cat->occupancy_rate . '%']);
        }

        fclose($handle);
        exit;
    }

    public function exportRevenueData(User $user, $filters)
    {
        $data = $this->getDetailedRevenueData($user, $filters);
        $metrics = $data['metrics'];

        $filename = "revenue_report_" . now()->format('YmdHis') . ".csv";
        $handle = fopen('php://output', 'w');
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // Headers
        fputcsv($handle, ['Category', 'Value']);
        
        // Metrics
        fputcsv($handle, ["Today's Revenue", $metrics['today']]);
        fputcsv($handle, ['MTD Revenue', $metrics['mtd']]);
        fputcsv($handle, ['YTD Revenue', $metrics['ytd']]);
        fputcsv($handle, ['ADR', $metrics['adr']]);
        fputcsv($handle, ['RevPAR', $metrics['revpar']]);
        
        fputcsv($handle, []); // Empty row
        
        // Source Breakdown
        fputcsv($handle, ['Revenue Source', 'Total Revenue']);
        foreach ($data['by_source'] as $source) {
            fputcsv($handle, [$source->name, $source->total]);
        }

        fputcsv($handle, []); // Empty row
        
        // Top Units
        fputcsv($handle, ['Unit Number', 'Revenue Generated']);
        foreach ($data['top_units'] as $unit) {
            fputcsv($handle, [$unit->number, $unit->revenue]);
        }

        fclose($handle);
        exit;
    }
}
