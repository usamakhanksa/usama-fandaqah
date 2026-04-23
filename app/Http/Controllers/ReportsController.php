<?php

namespace App\Http\Controllers;

use App\Models\SafeTransaction;
use App\Models\SavedReport;
use App\Http\Requests\StoreSafeTransactionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Carbon\Carbon;

class ReportsController extends Controller {
    
    public function index(Request $request) {
        $activeReport = $request->get('report', 'daily');
        $dateRange = $request->get('date_range', 'month'); // day, week, month, year

        // Generate dynamic payload based on the requested report
        $payload = $this->generateReportData($activeReport, $dateRange);

        return Inertia::render('Reports/Index', [
            'activeReport' => $activeReport,
            'dateRange' => $dateRange,
            'reportData' => $payload,
            'savedReports' => SavedReport::where('is_favorite', true)->get(),
        ]);
    }

    public function safeTransactions(Request $request) {
        $filters = $request->only(['search', 'type', 'start_date', 'end_date']);
        
        $transactions = SafeTransaction::query()
            ->when($filters['search'] ?? null, fn($q, $s) => $q->search($s))
            ->when($filters['type'] ?? null, fn($q, $t) => $q->where('type', $t))
            ->when($filters['start_date'] ?? null, fn($q, $d) => $q->where('transaction_date', '>=', $d))
            ->when($filters['end_date'] ?? null, fn($q, $d) => $q->where('transaction_date', '<=', $d))
            ->latest('transaction_date')
            ->paginate(15)
            ->withQueryString();

        $metrics = [
            'total_deposits' => SafeTransaction::where('type', 'deposit')->sum('amount'),
            'total_withdrawals' => SafeTransaction::where('type', 'withdrawal')->sum('amount'),
            'net_safe_balance' => SafeTransaction::where('type', 'deposit')->sum('amount') - SafeTransaction::where('type', 'withdrawal')->sum('amount'),
        ];

        return Inertia::render('Reports/SafeMovement', [
            'transactions' => $transactions,
            'filters' => $filters,
            'metrics' => $metrics,
        ]);
    }

    public function storeSafeTransaction(StoreSafeTransactionRequest $request) {
        $data = $request->validated();
        $data['reference_number'] = strtoupper(substr($data['type'], 0, 3)) . '-' . strtoupper(Str::random(8));
        $data['performed_by'] = auth()->user()->name ?? 'System Admin';

        SafeTransaction::create($data);
        return redirect()->back()->with('success', 'Transaction recorded successfully.');
    }

    public function deleteSafeTransaction(SafeTransaction $transaction) {
        $transaction->delete();
        return redirect()->back()->with('success', 'Transaction removed.');
    }

    // --- Private Aggregation Simulator ---
    private function generateReportData($type, $range) {
        // Mock data logic for the various requested reports
        // Each of these would typically involve complex joins on bookings, rooms, invoices, etc.
        
        switch ($type) {
            case 'occupancy':
                return [
                    'overall_ratio' => 84.5,
                    'available_rooms' => 120,
                    'occupied_rooms' => 101,
                    'breakdown' => [
                        ['type' => 'Royal Suite', 'ratio' => 95, 'occupied' => 19, 'total' => 20],
                        ['type' => 'Executive King', 'ratio' => 88, 'occupied' => 44, 'total' => 50],
                        ['type' => 'Standard Twin', 'ratio' => 76, 'occupied' => 38, 'total' => 50],
                    ]
                ];
            case 'revenues':
                return [
                    'total_revenue' => 450500.00,
                    'total_taxes' => 67575.00, // 15% VAT (Saudi)
                    'net_revenue' => 382925.00,
                    'breakdown' => [
                        ['category' => 'Room Accommodation', 'amount' => 320000, 'tax' => 48000],
                        ['category' => 'F&B Services', 'amount' => 85000, 'tax' => 12750],
                        ['category' => 'Spa & Wellness', 'amount' => 45500, 'tax' => 6825],
                    ]
                ];
            case 'daily':
                return [
                    'date' => Carbon::today()->format('Y-m-d'),
                    'arrivals' => 24,
                    'departures' => 18,
                    'in_house' => 83,
                    'revenue_collected' => 28500.00,
                    'cleaning_completed' => 45,
                ];
            case 'cleaning':
                return [
                    'cleaned_today' => 32,
                    'pending' => 8,
                    'inspected' => 28,
                    'movement' => [
                        ['staff' => 'Ahmed Al-Harbi', 'rooms' => 12, 'status' => 'Efficient'],
                        ['staff' => 'Saeed Khan', 'rooms' => 10, 'status' => 'Standard'],
                        ['staff' => 'Omar Bakar', 'rooms' => 10, 'status' => 'Standard'],
                    ]
                ];
            case 'maintenance':
                return [
                    'open_tickets' => 5,
                    'resolved_today' => 3,
                    'urgent' => 2,
                    'movement' => [
                        ['category' => 'Plumbing', 'count' => 12, 'avg_time' => '45 mins'],
                        ['category' => 'Electrical', 'count' => 8, 'avg_time' => '30 mins'],
                        ['category' => 'HVAC', 'count' => 15, 'avg_time' => '2 hours'],
                    ]
                ];
            case 'customers':
                return [
                    'new_registrations' => 15,
                    'returning_guests' => 45,
                    'loyalty_members' => 320,
                    'movement' => [
                        ['segment' => 'Corporate (KSA)', 'count' => 140],
                        ['segment' => 'Leisure', 'count' => 85],
                        ['segment' => 'Government', 'count' => 95],
                    ]
                ];
            case 'contracts':
                return [
                    'active_contracts' => 42,
                    'expiring_soon' => 4,
                    'new_this_month' => 2,
                    'contracts' => [
                        ['employee' => 'Salem Al-Dosari', 'role' => 'Front Desk', 'expiry' => '2026-12-31'],
                        ['employee' => 'Mona Ibrahim', 'role' => 'Housekeeping Mgr', 'expiry' => '2027-05-15'],
                    ]
                ];
            default:
                return ['message' => 'Detailed analytics for ' . str_replace('_', ' ', $type) . ' is being aggregated.'];
        }
    }
}
