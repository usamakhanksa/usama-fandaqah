<?php

namespace App\Http\Controllers;

use App\Models\ArAccount;
use App\Models\FinancialTransaction;
use App\Models\CompTransaction;
use App\Models\EodProcess;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\StoreArAccountRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class FinancialsController extends Controller
{
    /**
     * Display the financial dashboard.
     */
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'cashiering');
        
        $metrics = [
            'today_revenue' => FinancialTransaction::where('type', 'charge')->whereDate('transaction_date', today())->sum('amount'),
            'ar_balance' => ArAccount::sum('current_balance'),
            'today_comps' => CompTransaction::whereDate('date_posted', today())->sum('value_amount'),
            'yesterday_revenue' => FinancialTransaction::where('type', 'charge')->whereDate('transaction_date', today()->subDay())->sum('amount'),
        ];

        $data = [
            'tab' => $tab,
            'metrics' => $metrics,
        ];

        if ($tab === 'cashiering') {
            $data['transactions'] = FinancialTransaction::with('arAccount')
                ->latest()
                ->paginate(15)
                ->withQueryString();
        } elseif ($tab === 'ar') {
            $data['accounts'] = ArAccount::when($request->search, fn($q, $s) => $q->search($s))
                ->latest()
                ->paginate(15)
                ->withQueryString();
        } elseif ($tab === 'comps') {
            $data['comps'] = CompTransaction::latest()
                ->paginate(15)
                ->withQueryString();
        } elseif ($tab === 'eod') {
            $data['eods'] = EodProcess::latest()
                ->paginate(15)
                ->withQueryString();
            $data['latest_eod'] = EodProcess::latest('audit_date')->first();
        }

        return Inertia::render('Financials/Index', $data);
    }

    /**
     * Post a new cashiering transaction.
     */
    public function storeTransaction(StoreTransactionRequest $request)
    {
        $data = $request->validated();
        $data['receipt_number'] = 'RCPT-' . strtoupper(Str::random(10));
        
        DB::transaction(function () use ($data) {
            FinancialTransaction::create($data);
            
            // If CityLedger (AR), update AR Balance
            if (!empty($data['ar_account_id'])) {
                $ar = ArAccount::find($data['ar_account_id']);
                if ($data['type'] === 'charge') {
                    $ar->increment('current_balance', $data['amount']);
                } elseif ($data['type'] === 'payment') {
                    $ar->decrement('current_balance', $data['amount']);
                }
            }
        });
        
        return redirect()->back()->with('success', 'Transaction posted successfully.');
    }

    /**
     * Create a new AR Account.
     */
    public function storeArAccount(StoreArAccountRequest $request)
    {
        ArAccount::create($request->validated());
        return redirect()->back()->with('success', 'Accounts Receivable account created.');
    }

    /**
     * Update an AR Account.
     */
    public function updateArAccount(StoreArAccountRequest $request, $id)
    {
        ArAccount::findOrFail($id)->update($request->validated());
        return redirect()->back()->with('success', 'AR Account details updated.');
    }

    /**
     * Post a complimentary service.
     */
    public function storeComp(Request $request)
    {
        $data = $request->validate([
            'booking_reference' => 'required|string|max:50',
            'department' => 'required|in:rooms,f_and_b,spa,transport,other',
            'value_amount' => 'required|numeric|min:0',
            'reason' => 'required|string|max:500',
        ]);
        
        $data['approved_by'] = auth()->user()->name ?? 'Manager';
        $data['date_posted'] = today();
        
        CompTransaction::create($data);
        return redirect()->back()->with('success', 'Complimentary posting recorded.');
    }

    /**
     * Run the End of Day (EOD) night audit process.
     */
    public function runEod(Request $request)
    {
        // Business logic: Run for yesterday if not already done
        $auditDate = today()->subDay();
        
        if (EodProcess::where('audit_date', $auditDate)->where('status', 'completed')->exists()) {
            return redirect()->back()->withErrors(['eod' => "Night audit for {$auditDate->format('Y-m-d')} is already complete."]);
        }

        $revenue = FinancialTransaction::where('type', 'charge')->whereDate('transaction_date', $auditDate)->sum('amount');
        $payments = FinancialTransaction::where('type', 'payment')->whereDate('transaction_date', $auditDate)->sum('amount');
        $comps = CompTransaction::whereDate('date_posted', $auditDate)->sum('value_amount');

        EodProcess::updateOrCreate(
            ['audit_date' => $auditDate],
            [
                'total_revenue' => $revenue,
                'total_payments' => $payments,
                'total_comps' => $comps,
                'status' => 'completed',
                'run_by' => auth()->user()->name ?? 'System Admin',
                'completed_at' => now(),
            ]
        );

        return redirect()->back()->with('success', "End of Day process for {$auditDate->format('Y-m-d')} completed successfully.");
    }
}
