<?php

namespace App\Services\Reports;

use App\Models\Reservation;
use App\Models\Transaction;
use App\Models\Unit;
use App\Models\CashierShift;
use App\Models\ServiceLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DailyReportService extends ReportService
{
    protected $occupancyService;
    protected $revenueService;
    protected $adrRevparService;
    protected $paidOutsService;
    protected $commissionService;

    public function __construct(
        OccupancyCalculationService $occupancyService = null,
        RevenueCalculationService $revenueService = null,
        AdrRevparCalculationService $adrRevparService = null,
        PaidOutsReportService $paidOutsService = null,
        CommissionReportService $commissionService = null
    ) {
        parent::__construct();
        $this->occupancyService = $occupancyService ?? new OccupancyCalculationService();
        $this->revenueService = $revenueService ?? new RevenueCalculationService();
        $this->adrRevparService = $adrRevparService ?? new AdrRevparCalculationService();
        $this->paidOutsService = $paidOutsService ?? new PaidOutsReportService();
        $this->commissionService = $commissionService ?? new CommissionReportService();
    }

    /**
     * Generate complete daily report for a business date
     */
    public function generate($teamId, Carbon $businessDate)
    {
        $this->setTeamId($teamId);
        $this->occupancyService->setTeamId($teamId);
        $this->revenueService->setTeamId($teamId);
        $this->adrRevparService->setTeamId($teamId);
        $this->paidOutsService->setTeamId($teamId);
        $this->commissionService->setTeamId($teamId);

        return [
            'business_date' => $businessDate->toDateString(),
            'generated_at' => now()->toDateTimeString(),
            'occupancy' => $this->getOccupancySection($businessDate),
            'arrivals' => $this->getArrivalsSection($businessDate),
            'departures' => $this->getDeparturesSection($businessDate),
            'in_house' => $this->getInHouseSection($businessDate),
            'room_revenue' => $this->getRoomRevenueSection($businessDate),
            'fb_revenue' => $this->getFBRevenueSection($businessDate),
            'other_revenue' => $this->getOtherRevenueSection($businessDate),
            'total_revenue' => $this->getTotalRevenueSection($businessDate),
            'taxes' => $this->getTaxesSection($businessDate),
            'payments' => $this->getPaymentsSection($businessDate),
            'accounts_receivable' => $this->getAccountsReceivableSection($businessDate),
            'paid_outs' => $this->getPaidOutsSection($businessDate),
            'commissions' => $this->getCommissionsSection($businessDate),
            'cashier_summary' => $this->getCashierSummarySection($businessDate),
            'adr_revpar' => $this->getAdrRevparSection($businessDate),
            'comparison' => $this->getComparisonSection($businessDate),
        ];
    }

    /**
     * 1. OCCUPANCY Section
     */
    protected function getOccupancySection(Carbon $date)
    {
        $totalRooms = Unit::where('team_id', $this->teamId)
            ->whereIn('status', ['available', 'occupied', 'dirty', 'clean'])
            ->count();

        $occupied = Reservation::where('team_id', $this->teamId)
            ->where('status', 'checked-in')
            ->whereDate('check_in', '<=', $date)
            ->whereDate('check_out', '>', $date)
            ->count();

        // Out of Order / Out of Service rooms
        $ooo = Unit::where('team_id', $this->teamId)
            ->where('status', 'out-of-order')
            ->count();

        $outOfService = DB::table('unit_maintenances')
            ->where('team_id', $this->teamId)
            ->whereDate('start_date', '<=', $date)
            ->where(function($q) use ($date) {
                $q->whereNull('end_date')
                  ->orWhereDate('end_date', '>=', $date);
            })
            ->count();

        $available = $totalRooms - $occupied - $ooo;
        $occupancyRate = $totalRooms > 0 ? round(($occupied / $totalRooms) * 100, 2) : 0;

        // Double occupancy calculation
        $totalGuests = Reservation::where('team_id', $this->teamId)
            ->where('status', 'checked-in')
            ->whereDate('check_in', '<=', $date)
            ->whereDate('check_out', '>', $date)
            ->sum('adults');

        $doubleOccupancyRate = $occupied > 0 ? round(($totalGuests / $occupied), 2) : 0;

        return [
            'total_rooms' => $totalRooms,
            'available' => max(0, $available),
            'occupied' => $occupied,
            'ooo' => $ooo,
            'out_of_service' => $outOfService,
            'occupancy_rate' => $occupancyRate,
            'double_occupancy_rate' => $doubleOccupancyRate,
            'total_guests' => $totalGuests,
        ];
    }

    /**
     * 2. ARRIVALS Section
     */
    protected function getArrivalsSection(Carbon $date)
    {
        $arrivals = Reservation::where('team_id', $this->teamId)
            ->whereDate('check_in', $date)
            ->with(['customer', 'unit', 'source'])
            ->get();

        return [
            'count' => $arrivals->count(),
            'list' => $arrivals->map(function($res) {
                return [
                    'id' => $res->id,
                    'number' => $res->number,
                    'guest_name' => $res->customer ? $res->customer->first_name . ' ' . $res->customer->last_name : 'N/A',
                    'room_number' => $res->unit ? $res->unit->unit_number : 'N/A',
                    'status' => $res->status,
                    'source' => $res->source ? $res->source->name : 'Direct',
                    'adults' => $res->adults,
                    'children' => $res->children,
                    'nights' => $res->check_in->diffInDays($res->check_out),
                ];
            }),
        ];
    }

    /**
     * 3. DEPARTURES Section
     */
    protected function getDeparturesSection(Carbon $date)
    {
        $departures = Reservation::where('team_id', $this->teamId)
            ->whereDate('check_out', $date)
            ->with(['customer', 'unit', 'source'])
            ->get();

        return [
            'count' => $departures->count(),
            'list' => $departures->map(function($res) {
                return [
                    'id' => $res->id,
                    'number' => $res->number,
                    'guest_name' => $res->customer ? $res->customer->first_name . ' ' . $res->customer->last_name : 'N/A',
                    'room_number' => $res->unit ? $res->unit->unit_number : 'N/A',
                    'status' => $res->status,
                    'source' => $res->source ? $res->source->name : 'Direct',
                    'nights' => $res->check_in->diffInDays($res->check_out),
                ];
            }),
        ];
    }

    /**
     * 4. IN_HOUSE Section
     */
    protected function getInHouseSection(Carbon $date)
    {
        $inHouse = Reservation::where('team_id', $this->teamId)
            ->where('status', 'checked-in')
            ->whereDate('check_in', '<=', $date)
            ->whereDate('check_out', '>', $date)
            ->with(['customer', 'unit'])
            ->get();

        $roomRevenue = Transaction::where('team_id', $this->teamId)
            ->where('type', 'withdraw')
            ->whereDate('business_date', $date)
            ->whereJsonContains('meta->category', 'reservation')
            ->sum('amount');

        return [
            'count' => $inHouse->count(),
            'room_revenue' => round($roomRevenue, 2),
            'list' => $inHouse->map(function($res) use ($date) {
                return [
                    'id' => $res->id,
                    'number' => $res->number,
                    'guest_name' => $res->customer ? $res->customer->first_name . ' ' . $res->customer->last_name : 'N/A',
                    'room_number' => $res->unit ? $res->unit->unit_number : 'N/A',
                    'check_in' => $res->check_in->toDateString(),
                    'check_out' => $res->check_out->toDateString(),
                    'nights_stayed' => $res->check_in->diffInDays($date),
                    'nights_remaining' => $date->diffInDays($res->check_out),
                ];
            }),
        ];
    }

    /**
     * 5. ROOM_REVENUE Section
     */
    protected function getRoomRevenueSection(Carbon $date)
    {
        $roomRevenue = Transaction::where('team_id', $this->teamId)
            ->where('type', 'withdraw')
            ->whereDate('business_date', $date)
            ->whereJsonContains('meta->category', 'reservation')
            ->sum('amount');

        // Revenue by room type
        $byRoomType = DB::table('transactions')
            ->join('reservations', function($join) {
                $join->on('transactions.payable_id', '=', 'reservations.id')
                    ->where('transactions.payable_type', '=', Reservation::class);
            })
            ->join('units', 'reservations.unit_id', '=', 'units.id')
            ->join('unit_categories', 'units.unit_category_id', '=', 'unit_categories.id')
            ->where('transactions.team_id', $this->teamId)
            ->where('transactions.type', 'withdraw')
            ->whereDate('transactions.business_date', $date)
            ->whereJsonContains('transactions.meta->category', 'reservation')
            ->select(
                'unit_categories.name',
                DB::raw('SUM(transactions.amount) as total'),
                DB::raw('COUNT(DISTINCT reservations.id) as reservations_count')
            )
            ->groupBy('unit_categories.id', 'unit_categories.name')
            ->get();

        $totalRooms = Reservation::where('team_id', $this->teamId)
            ->where('status', 'checked-in')
            ->whereDate('check_in', '<=', $date)
            ->whereDate('check_out', '>', $date)
            ->count();

        $averageRate = $totalRooms > 0 ? round($roomRevenue / $totalRooms, 2) : 0;

        return [
            'total' => round($roomRevenue, 2),
            'by_room_type' => $byRoomType,
            'average_rate' => $averageRate,
        ];
    }

    /**
     * 6. FB_REVENUE Section (Food & Beverage)
     */
    protected function getFBRevenueSection(Carbon $date)
    {
        $fbRevenue = ServiceLog::where('team_id', $this->teamId)
            ->whereDate('created_at', $date)
            ->whereHas('service', function($q) {
                $q->whereIn('category', ['food', 'beverage', 'restaurant', 'minibar']);
            })
            ->sum('price');

        // Revenue by outlet/service
        $byOutlet = ServiceLog::where('team_id', $this->teamId)
            ->whereDate('created_at', $date)
            ->with('service')
            ->get()
            ->groupBy('service.category')
            ->map(function($group) {
                return [
                    'outlet' => $group->first()->service->category ?? 'Other',
                    'total' => round($group->sum('price'), 2),
                ];
            })
            ->values();

        return [
            'total' => round($fbRevenue, 2),
            'by_outlet' => $byOutlet,
        ];
    }

    /**
     * 7. OTHER_REVENUE Section
     */
    protected function getOtherRevenueSection(Carbon $date)
    {
        // Other services revenue (not room, not FB)
        $otherRevenue = ServiceLog::where('team_id', $this->teamId)
            ->whereDate('created_at', $date)
            ->whereHas('service', function($q) {
                $q->whereNotIn('category', ['food', 'beverage', 'restaurant', 'minibar', 'room']);
            })
            ->sum('price');

        // By category
        $byCategory = ServiceLog::where('team_id', $this->teamId)
            ->whereDate('created_at', $date)
            ->with('service')
            ->get()
            ->whereNotIn('service.category', ['food', 'beverage', 'restaurant', 'minibar', 'room'])
            ->groupBy('service.category')
            ->map(function($group) {
                return [
                    'category' => $group->first()->service->category ?? 'Other',
                    'total' => round($group->sum('price'), 2),
                ];
            })
            ->values();

        return [
            'total' => round($otherRevenue, 2),
            'by_category' => $byCategory,
        ];
    }

    /**
     * 8. TOTAL_REVENUE Section
     */
    protected function getTotalRevenueSection(Carbon $date)
    {
        $roomRevenue = Transaction::where('team_id', $this->teamId)
            ->where('type', 'withdraw')
            ->whereDate('business_date', $date)
            ->whereJsonContains('meta->category', 'reservation')
            ->sum('amount');

        $serviceRevenue = ServiceLog::where('team_id', $this->teamId)
            ->whereDate('created_at', $date)
            ->sum('price');

        $total = $roomRevenue + $serviceRevenue;

        return [
            'room_revenue' => round($roomRevenue, 2),
            'service_revenue' => round($serviceRevenue, 2),
            'total' => round($total, 2),
        ];
    }

    /**
     * 9. TAXES Section
     */
    protected function getTaxesSection(Carbon $date)
    {
        $transactions = Transaction::where('team_id', $this->teamId)
            ->whereDate('business_date', $date)
            ->get();

        $vatCollected = $transactions->sum('tax_amount');
        $tourismTax = $transactions->sum('tourism_tax_amount');
        $accommodationTax = $transactions->sum('accommodation_tax_amount');

        return [
            'vat_collected' => round($vatCollected, 2),
            'municipality_fee' => round($accommodationTax, 2),
            'tourism_levy' => round($tourismTax, 2),
            'total_taxes' => round($vatCollected + $tourismTax + $accommodationTax, 2),
        ];
    }

    /**
     * 10. PAYMENTS Section
     */
    protected function getPaymentsSection(Carbon $date)
    {
        $payments = Transaction::where('team_id', $this->teamId)
            ->where('type', 'deposit')
            ->whereDate('created_at', $date)
            ->get();

        $byMethod = $payments->groupBy(function($trans) {
            return $trans->meta['payment_type'] ?? 'unknown';
        })->map(function($group, $method) {
            return [
                'method' => ucfirst($method),
                'total' => round($group->sum('amount'), 2),
                'count' => $group->count(),
            ];
        })->values();

        return [
            'by_method' => $byMethod,
            'total' => round($payments->sum('amount'), 2),
            'count' => $payments->count(),
        ];
    }

    /**
     * 11. ACCOUNTS_RECEIVABLE Section
     */
    protected function getAccountsReceivableSection(Carbon $date)
    {
        // City Ledger - unpaid balances
        $cityLedger = Reservation::where('team_id', $this->teamId)
            ->where('payment_status', '!=', 'paid')
            ->whereDate('check_out', '<=', $date)
            ->sum('balance');

        // Company accounts
        $companyAR = DB::table('reservations')
            ->join('companies', 'reservations.company_id', '=', 'companies.id')
            ->where('reservations.team_id', $this->teamId)
            ->where('reservations.payment_status', '!=', 'paid')
            ->whereDate('reservations.check_out', '<=', $date)
            ->select('companies.name', DB::raw('SUM(reservations.balance) as balance'))
            ->groupBy('companies.id', 'companies.name')
            ->get();

        // Travel Agent accounts
        $travelAgentAR = DB::table('reservations')
            ->join('sources', 'reservations.source_id', '=', 'sources.id')
            ->where('reservations.team_id', $this->teamId)
            ->where('reservations.payment_status', '!=', 'paid')
            ->where('sources.type', 'travel_agent')
            ->whereDate('reservations.check_out', '<=', $date)
            ->select('sources.name', DB::raw('SUM(reservations.balance) as balance'))
            ->groupBy('sources.id', 'sources.name')
            ->get();

        return [
            'city_ledger' => round($cityLedger, 2),
            'company' => $companyAR,
            'travel_agent' => $travelAgentAR,
            'total' => round($cityLedger + $companyAR->sum('balance') + $travelAgentAR->sum('balance'), 2),
        ];
    }

    /**
     * 12. PAID_OUTS Section
     */
    protected function getPaidOutsSection(Carbon $date)
    {
        $paidOuts = Transaction::where('team_id', $this->teamId)
            ->where('type', 'withdraw')
            ->whereDate('created_at', $date)
            ->whereJsonContains('meta->is_paid_out', true)
            ->get();

        return [
            'list' => $paidOuts->map(function($trans) {
                return [
                    'id' => $trans->id,
                    'description' => $trans->description,
                    'amount' => $trans->amount,
                    'category' => $trans->meta['category'] ?? 'Other',
                    'created_by' => $trans->creator ? $trans->creator->name : 'N/A',
                ];
            }),
            'total' => round($paidOuts->sum('amount'), 2),
        ];
    }

    /**
     * 13. COMMISSIONS Section
     */
    protected function getCommissionsSection(Carbon $date)
    {
        // Commissions earned for reservations on this date
        $commissions = DB::table('reservations')
            ->join('sources', 'reservations.source_id', '=', 'sources.id')
            ->where('reservations.team_id', $this->teamId)
            ->whereDate('reservations.check_in', $date)
            ->whereNotNull('sources.commission_rate')
            ->select(
                'sources.name',
                'sources.commission_rate',
                DB::raw('SUM(reservations.total_amount) as total_revenue'),
                DB::raw('SUM(reservations.total_amount * sources.commission_rate / 100) as commission_amount')
            )
            ->groupBy('sources.id', 'sources.name', 'sources.commission_rate')
            ->get();

        return [
            'calculated' => $commissions,
            'total' => round($commissions->sum('commission_amount'), 2),
        ];
    }

    /**
     * 14. CASHIER_SUMMARY Section
     */
    protected function getCashierSummarySection(Carbon $date)
    {
        $shifts = CashierShift::where('team_id', $this->teamId)
            ->whereDate('shift_date', $date)
            ->with('user')
            ->get();

        return [
            'shifts' => $shifts->map(function($shift) {
                $deposits = Transaction::where('cashier_shift_id', $shift->id)
                    ->where('type', 'deposit')
                    ->sum('amount');
                
                $withdrawals = Transaction::where('cashier_shift_id', $shift->id)
                    ->where('type', 'withdraw')
                    ->sum('amount');

                return [
                    'cashier_name' => $shift->user ? $shift->user->name : 'N/A',
                    'opened_at' => $shift->opened_at,
                    'closed_at' => $shift->closed_at,
                    'opening_balance' => $shift->opening_balance,
                    'closing_balance' => $shift->closing_balance,
                    'system_balance' => $shift->system_balance,
                    'variance' => $shift->variance,
                    'deposits' => round($deposits, 2),
                    'withdrawals' => round($withdrawals, 2),
                    'status' => $shift->status,
                ];
            }),
        ];
    }

    /**
     * 15. ADR_REVPAR Section
     */
    protected function getAdrRevparSection(Carbon $date)
    {
        $totalRooms = Unit::where('team_id', $this->teamId)->count();
        
        $occupiedRooms = Reservation::where('team_id', $this->teamId)
            ->where('status', 'checked-in')
            ->whereDate('check_in', '<=', $date)
            ->whereDate('check_out', '>', $date)
            ->count();

        $roomRevenue = Transaction::where('team_id', $this->teamId)
            ->where('type', 'withdraw')
            ->whereDate('business_date', $date)
            ->whereJsonContains('meta->category', 'reservation')
            ->sum('amount');

        $adr = $occupiedRooms > 0 ? round($roomRevenue / $occupiedRooms, 2) : 0;
        $revpar = $totalRooms > 0 ? round($roomRevenue / $totalRooms, 2) : 0;

        return [
            'adr' => $adr,
            'revpar' => $revpar,
            'occupied_rooms' => $occupiedRooms,
            'total_rooms' => $totalRooms,
            'room_revenue' => round($roomRevenue, 2),
        ];
    }

    /**
     * 16. COMPARISON Section
     */
    protected function getComparisonSection(Carbon $date)
    {
        // Compare with same day last year
        $lastYear = $date->copy()->subYear();
        $yesterday = $date->copy()->subDay();

        return [
            'vs_yesterday' => $this->getComparisonData($yesterday, $date),
            'vs_last_year' => $this->getComparisonData($lastYear, $date),
        ];
    }

    /**
     * Helper: Get comparison data
     */
    protected function getComparisonData(Carbon $compareDate, Carbon $currentDate)
    {
        // Current metrics
        $currentRevenue = Transaction::where('team_id', $this->teamId)
            ->whereDate('business_date', $currentDate)
            ->sum('amount');

        $currentOccupancy = Reservation::where('team_id', $this->teamId)
            ->where('status', 'checked-in')
            ->whereDate('check_in', '<=', $currentDate)
            ->whereDate('check_out', '>', $currentDate)
            ->count();

        // Compare metrics
        $compareRevenue = Transaction::where('team_id', $this->teamId)
            ->whereDate('business_date', $compareDate)
            ->sum('amount');

        $compareOccupancy = Reservation::where('team_id', $this->teamId)
            ->where('status', 'checked-in')
            ->whereDate('check_in', '<=', $compareDate)
            ->whereDate('check_out', '>', $compareDate)
            ->count();

        $revenueDiff = $currentRevenue - $compareRevenue;
        $revenuePercentChange = $compareRevenue > 0 ? round((($currentRevenue - $compareRevenue) / $compareRevenue) * 100, 2) : 0;

        $occupancyDiff = $currentOccupancy - $compareOccupancy;
        $occupancyPercentChange = $compareOccupancy > 0 ? round((($currentOccupancy - $compareOccupancy) / $compareOccupancy) * 100, 2) : 0;

        return [
            'date' => $compareDate->toDateString(),
            'revenue' => [
                'current' => round($currentRevenue, 2),
                'compare' => round($compareRevenue, 2),
                'difference' => round($revenueDiff, 2),
                'percent_change' => $revenuePercentChange,
            ],
            'occupancy' => [
                'current' => $currentOccupancy,
                'compare' => $compareOccupancy,
                'difference' => $occupancyDiff,
                'percent_change' => $occupancyPercentChange,
            ],
        ];
    }

    /**
     * Export report to various formats
     */
    public function export($report, $format = 'pdf')
    {
        switch ($format) {
            case 'pdf':
                return $this->exportToPDF($report);
            case 'excel':
                return $this->exportToExcel($report);
            default:
                return $this->exportToArray($report);
        }
    }

    protected function exportToPDF($report)
    {
        $pdf = \PDF::loadView('reports.daily_report_pdf', ['report' => $report]);
        return $pdf->download("daily_report_{$report['business_date']}.pdf");
    }

    protected function exportToExcel($report)
    {
        // Use Laravel Excel or simple CSV
        $filename = "daily_report_{$report['business_date']}.csv";
        
        $handle = fopen('php://output', 'w');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        // Write headers and data
        fputcsv($handle, ['Daily Business Report', $report['business_date']]);
        fputcsv($handle, []);
        
        // Occupancy section
        fputcsv($handle, ['OCCUPANCY']);
        fputcsv($handle, ['Total Rooms', $report['occupancy']['total_rooms']]);
        fputcsv($handle, ['Occupied', $report['occupancy']['occupied']]);
        fputcsv($handle, ['Available', $report['occupancy']['available']]);
        fputcsv($handle, ['Occupancy Rate', $report['occupancy']['occupancy_rate'] . '%']);
        fputcsv($handle, []);
        
        // Revenue section
        fputcsv($handle, ['REVENUE']);
        fputcsv($handle, ['Room Revenue', $report['room_revenue']['total'] . ' SR']);
        fputcsv($handle, ['FB Revenue', $report['fb_revenue']['total'] . ' SR']);
        fputcsv($handle, ['Other Revenue', $report['other_revenue']['total'] . ' SR']);
        fputcsv($handle, ['Total Revenue', $report['total_revenue']['total'] . ' SR']);
        fputcsv($handle, []);
        
        // ADR/RevPAR
        fputcsv($handle, ['PERFORMANCE METRICS']);
        fputcsv($handle, ['ADR', $report['adr_revpar']['adr'] . ' SR']);
        fputcsv($handle, ['RevPAR', $report['adr_revpar']['revpar'] . ' SR']);
        
        fclose($handle);
        exit;
    }

    protected function exportToArray($report)
    {
        return $report;
    }
}
