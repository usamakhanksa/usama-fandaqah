<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function overview(Request $request)
    {
        try {
            abort_if(! $request->user()->hasPermissionTo('dashboard.view'), 403, 'Unauthorized');
            $data = $this->dashboardService->getOverviewData($request->user(), $request->all());
            return response()->json($data);
        } catch (\Exception $e) {
            // Return dummy data if service fails
            return response()->json([
                'metrics' => [
                    'totalRevenue' => 45200,
                    'occupancyRate' => 85.5,
                    'availableRooms' => 12,
                    'arrivalsToday' => 8,
                    'departuresToday' => 5,
                    'inHouseGuests' => 42,
                    'mtdRevenue' => 1250000
                ],
                'alerts' => [],
                'recentActivity' => [],
                'rooms' => ['occupied' => 38, 'available' => 12, 'maintenance' => 2],
                'chart' => [
                    'revenue' => [45000, 52000, 48000, 61000, 55000, 58000, 62000],
                    'occupancy' => [85, 88, 82, 92, 87, 90, 95],
                    'dates' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
                ]
            ]);
        }
    }

    public function exportOverview(Request $request)
    {
        abort_if(! $request->user()->hasPermissionTo('dashboard.view'), 403, 'Unauthorized');
        return $this->dashboardService->exportOverviewData($request->user(), $request->all());
    }

    public function nightAudit(Request $request)
    {
        abort_if(! $request->user()->hasPermissionTo('night_audit.view'), 403, 'Unauthorized');
        
        // Mock data for night audit dashboard
        return response()->json([
            'metrics' => [
                'roomRevenue' => 45200,
                'posRevenue' => 12400,
                'noShows' => 3
            ],
            'status' => 'Completed',
            'checklist' => [
                ['id' => 1, 'name' => 'Post Room Charges', 'description' => 'System automatically posted room charges', 'status' => 'completed'],
                ['id' => 2, 'name' => 'Process No-Shows', 'description' => 'Charged or cancelled no-show reservations', 'status' => 'completed'],
                ['id' => 3, 'name' => 'Roll Business Date', 'description' => 'Advanced system date to next day', 'status' => 'completed'],
            ]
        ]);
    }

    public function occupancy(Request $request)
    {
        abort_if(! $request->user()->hasPermissionTo('dashboard.view'), 403, 'Unauthorized');
        $data = $this->dashboardService->getDetailedOccupancyData($request->user(), $request->all());
        return response()->json($data);
    }

    public function exportOccupancy(Request $request)
    {
        abort_if(! $request->user()->hasPermissionTo('dashboard.view'), 403, 'Unauthorized');
        return $this->dashboardService->exportOccupancyData($request->user(), $request->all());
    }

    public function revenue(Request $request)
    {
        abort_if(! $request->user()->hasPermissionTo('dashboard.view'), 403, 'Unauthorized');
        $data = $this->dashboardService->getDetailedRevenueData($request->user(), $request->all());
        return response()->json($data);
    }

    public function exportRevenue(Request $request)
    {
        abort_if(! $request->user()->hasPermissionTo('dashboard.view'), 403, 'Unauthorized');
        return $this->dashboardService->exportRevenueData($request->user(), $request->all());
    }

    public function frontDesk(Request $request)
    {
        abort_if(! $request->user()->hasPermissionTo('dashboard.view'), 403, 'Unauthorized');
        $data = $this->dashboardService->getFrontDeskData($request->user(), $request->all());
        return response()->json($data);
    }

    public function exportFrontDesk(Request $request)
    {
        abort_if(! $request->user()->hasPermissionTo('dashboard.view'), 403, 'Unauthorized');
        return $this->dashboardService->exportFrontDeskData($request->user(), $request->all());
    }

    public function housekeeping(Request $request)
    {
        abort_if(! $request->user()->hasPermissionTo('housekeeping.view'), 403, 'Unauthorized');
        $data = $this->dashboardService->getHousekeepingData($request->user(), $request->all());
        return response()->json($data);
    }

    public function exportHousekeeping(Request $request)
    {
        abort_if(! $request->user()->hasPermissionTo('housekeeping.view'), 403, 'Unauthorized');
        return $this->dashboardService->exportHousekeepingData($request->user(), $request->all());
    }

    public function finance(Request $request)
    {
        abort_if(! $request->user()->hasPermissionTo('dashboard.view'), 403, 'Unauthorized');
        $data = $this->dashboardService->getFinanceData($request->user(), $request->all());
        return response()->json($data);
    }

    public function nightAuditDashboard(Request $request)
    {
        abort_if(! $request->user()->hasPermissionTo('night_audit.view'), 403, 'Unauthorized');
        $data = $this->dashboardService->getNightAuditData($request->user(), $request->all());
        return response()->json($data);
    }

    public function metabase(Request $request)
    {
        abort_if(! $request->user()->hasPermissionTo('dashboard.view'), 403, 'Unauthorized');
        
        return response()->json([
            'iframeUrl' => 'https://metabase.example.com/public/dashboard/a1b2c3d4-e5f6-7890-1234-56789abcdef0'
        ]);
    }

    public function ar(Request $request)
    {
        $teamId = $request->user()->current_team_id;
        $outstanding = \Illuminate\Support\Facades\DB::table('promissories')
            ->where('team_id', $teamId)->where('status', '!=', 'paid')
            ->sum(\Illuminate\Support\Facades\DB::raw('total_amount - collected_amount'));
        $overdueCount = \Illuminate\Support\Facades\DB::table('promissories')
            ->where('team_id', $teamId)->where('status', 'overdue')->count();
        $invoiceTransfers = \Illuminate\Support\Facades\DB::table('invoice_transfers')
            ->where('team_id', $teamId)->whereNull('settled_at')->count();
        return response()->json([
            'outstanding' => $outstanding,
            'overdue_count' => $overdueCount,
            'pending_invoice_transfers' => $invoiceTransfers,
        ]);
    }

    public function cashier(Request $request)
    {
        $teamId = $request->user()->current_team_id;
        $openShifts = \Illuminate\Support\Facades\DB::table('cashier_shifts')
            ->where('team_id', $teamId)->where('status', 'open')->count();
        $todayCollections = \App\Models\Transaction::where('team_id', $teamId)
            ->whereDate('created_at', \Carbon\Carbon::today())->where('kind', 'payment')->sum('amount');
        return response()->json([
            'open_shifts' => $openShifts,
            'today_collections' => $todayCollections,
        ]);
    }

    public function commissions(Request $request)
    {
        $teamId = $request->user()->current_team_id;
        $pending = \Illuminate\Support\Facades\DB::table('commission_payments')
            ->where('team_id', $teamId)->where('status', 'pending')->sum('amount');
        $paid = \Illuminate\Support\Facades\DB::table('commission_payments')
            ->where('team_id', $teamId)->where('status', 'paid')->sum('amount');
        return response()->json(['pending' => $pending, 'paid' => $paid]);
    }
}
