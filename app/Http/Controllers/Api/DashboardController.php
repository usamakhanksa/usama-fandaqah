<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DashboardNotice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the dashboard metrics and active notices.
     */
    public function index()
    {
        // Fetch active notices for the dashboard display
        $notices = DashboardNotice::active()->latest()->take(5)->get();

        // Simulated highly realistic PMS Aggregated Data for visual UI
        // In a real scenario, these would be calculated from Reservations, Rooms, etc.
        $metrics = [
            'total_properties' => 124,
            'occupancy_rate' => 92.5,
            'monthly_revenue' => 2450000, // SAR
            'pending_maintenance' => 18,
            'arrivals_today' => 42,
            'departures_today' => 38,
        ];

        // Simulated chart data for revenue growth
        $revenueData = [
            ['month' => 'Jan', 'amount' => 2100000],
            ['month' => 'Feb', 'amount' => 2250000],
            ['month' => 'Mar', 'amount' => 2400000],
            ['month' => 'Apr', 'amount' => 2450000],
        ];

        return response()->json([
            'notices' => $notices,
            'metrics' => $metrics,
            'revenueData' => $revenueData,
        ]);
    }
}
