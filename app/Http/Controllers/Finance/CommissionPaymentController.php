<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\Company;
use App\Models\CommissionPayment;
use App\Services\CommissionCalculationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CommissionPaymentController extends Controller
{
    protected $commissionService;

    public function __construct(CommissionCalculationService $commissionService)
    {
        $this->commissionService = $commissionService;
    }

    public function index(Request $request)
    {
        $payments = CommissionPayment::with(['travelAgent', 'bank', 'creator'])
            ->orderBy('payment_date', 'desc')
            ->paginate(10);

        return Inertia::render('Finance/CommissionPayments/Index', [
            'payments' => $payments
        ]);
    }

    public function create()
    {
        // Companies that are travel agents (assuming they have specific type or just all companies)
        $agents = Company::orderBy('name')->get();
        $banks = Bank::where('is_active', true)->orderBy('name')->get();

        return Inertia::render('Finance/CommissionPayments/Create', [
            'agents' => $agents,
            'banks' => $banks
        ]);
    }

    public function calculate(Request $request)
    {
        $request->validate([
            'agent_id' => 'required|exists:companies,id',
            'from' => 'required|date',
            'to' => 'required|date',
        ]);

        $calculation = $this->commissionService->calculateAgentCommission(
            $request->agent_id,
            $request->from,
            $request->to
        );

        return response()->json($calculation);
    }

    public function store(Request $request)
    {
        $request->validate([
            'agent_id' => 'required|exists:companies,id',
            'from' => 'required|date',
            'to' => 'required|date',
            'payment_method' => 'required|in:cash,card,bank_transfer,cheque',
            'bank_id' => 'nullable|exists:banks,id',
            'reference_number' => 'nullable|string',
            'payment_date' => 'required|date',
            'total_paid' => 'required|numeric',
            'notes' => 'nullable|string',
        ]);

        $payment = $this->commissionService->generateCommissionPayment(
            $request->agent_id,
            $request->from,
            $request->to,
            [
                'payment_method' => $request->payment_method,
                'bank_id' => $request->bank_id,
                'reference_number' => $request->reference_number,
                'payment_date' => $request->payment_date,
                'total_paid' => $request->total_paid,
                'notes' => $request->notes,
                'status' => 'paid'
            ]
        );

        return redirect()->route('finance.commission-payments.show', $payment->id)
            ->with('success', 'Commission payment recorded successfully.');
    }

    public function show(CommissionPayment $commissionPayment)
    {
        return Inertia::render('Finance/CommissionPayments/Show', [
            'payment' => $commissionPayment->load(['travelAgent', 'bank', 'creator', 'details.reservation.guest'])
        ]);
    }

    public function export(Request $request)
    {
        // Placeholder for export logic
        return response()->json(['message' => 'Export functionality will be implemented soon.']);
    }

    public function destroy(CommissionPayment $commissionPayment)
    {
        $commissionPayment->delete();
        return redirect()->route('finance.commission-payments.index')->with('success', 'Commission payment deleted.');
    }
}
