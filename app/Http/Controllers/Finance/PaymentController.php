<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Guest;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\CashierShift;
use App\Services\Finance\PaymentService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
        $this->middleware('permission:payments.view')->only(['index', 'show', 'print', 'export']);
        $this->middleware('permission:payments.create')->only(['create', 'store']);
        $this->middleware('permission:payments.edit')->only(['edit', 'update']);
        $this->middleware('permission:payments.delete')->only(['destroy']);
        $this->middleware('permission:payments.confirm')->only(['confirm']);
        $this->middleware('permission:payments.cancel')->only(['cancel']);
        $this->middleware('permission:payments.reverse')->only(['reverse']);
    }

    public function index(Request $request)
    {
        $query = Payment::query()
            ->with(['guest', 'reservation', 'cashierShift'])
            ->forTeam(auth()->user()->current_team_id);

        // Filters
        if ($request->filled('date_from')) {
            $query->whereDate('payment_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('payment_date', '<=', $request->date_to);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('method')) {
            $query->where('payment_method', $request->method);
        }
        if ($request->filled('type')) {
            $query->where('payment_type', $request->type);
        }
        if ($request->filled('guest_id')) {
            $query->where('guest_id', $request->guest_id);
        }
        if ($request->filled('reservation_id')) {
            $query->where('reservation_id', $request->reservation_id);
        }
        if ($request->filled('shift_id')) {
            $query->where('cashier_shift_id', $request->shift_id);
        }

        $payments = $query->latest('payment_date')->paginate(10)->withQueryString();

        return Inertia::render('Finance/Payments/Index', [
            'payments' => $payments,
            'filters' => $request->all(),
            'stats' => [
                'today_total' => Payment::forTeam(auth()->user()->current_team_id)->whereDate('payment_date', now())->confirmed()->sum('amount'),
                'week_total' => Payment::forTeam(auth()->user()->current_team_id)->whereBetween('payment_date', [now()->startOfWeek(), now()->endOfWeek()])->confirmed()->sum('amount'),
                'month_total' => Payment::forTeam(auth()->user()->current_team_id)->whereMonth('payment_date', now()->month)->confirmed()->sum('amount'),
                'pending_count' => Payment::forTeam(auth()->user()->current_team_id)->pending()->count(),
            ]
        ]);
    }

    public function create()
    {
        return Inertia::render('Finance/Payments/Create', [
            'guests' => Guest::forTeam(auth()->user()->current_team_id)->get(['id', 'name']),
            'reservations' => Reservation::forTeam(auth()->user()->current_team_id)->with('guest')->latest()->take(50)->get(),
            'shifts' => CashierShift::forTeam(auth()->user()->current_team_id)->where('status', 'open')->get(),
            'companies' => Company::forTeam(auth()->user()->current_team_id)->get(['id', 'name']),
            'invoices' => Invoice::forTeam(auth()->user()->current_team_id)->where('status', 'unpaid')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'reservation_id' => 'nullable|exists:reservations,id',
            'guest_id' => 'nullable|exists:guests,id',
            'company_id' => 'nullable|exists:companies,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'payment_type' => 'required|string',
            'reference_number' => 'nullable|string',
            'bank_name' => 'nullable|string',
            'cheque_number' => 'nullable|string',
            'card_last_four' => 'nullable|string|max:4',
            'card_authorization' => 'nullable|string',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'cashier_shift_id' => 'nullable|exists:cashier_shifts,id',
            'is_advance' => 'boolean',
            'is_deposit' => 'boolean',
            'currency' => 'required|string',
            'exchange_rate' => 'required|numeric',
            'original_amount' => 'nullable|numeric',
        ]);

        $this->paymentService->validatePayment($data);

        $data['team_id'] = auth()->user()->current_team_id;
        $data['created_by'] = auth()->id();
        $data['status'] = $request->boolean('confirm_now') ? 'confirmed' : 'pending';

        $payment = Payment::create($data);

        if ($payment->status === 'confirmed') {
            $payment->confirmed_at = now();
            $payment->confirmed_by = auth()->id();
            $payment->save();
            $this->paymentService->processPayment($payment);
        }

        return redirect()->route('finance.payments.index')->with('success', 'Payment created successfully.');
    }

    public function show(Payment $payment)
    {
        $payment->load(['guest', 'reservation', 'company', 'invoice', 'receipt', 'cashierShift', 'transaction', 'createdBy', 'confirmedBy', 'cancelledBy']);
        
        return Inertia::render('Finance/Payments/Show', [
            'payment' => $payment
        ]);
    }

    public function edit(Payment $payment)
    {
        if (!$payment->isEditable()) {
            return redirect()->back()->with('error', 'Only pending payments can be edited.');
        }

        return Inertia::render('Finance/Payments/Edit', [
            'payment' => $payment,
            'guests' => Guest::forTeam(auth()->user()->current_team_id)->get(['id', 'name']),
            'reservations' => Reservation::forTeam(auth()->user()->current_team_id)->with('guest')->latest()->take(50)->get(),
            'shifts' => CashierShift::forTeam(auth()->user()->current_team_id)->where('status', 'open')->get(),
            'companies' => Company::forTeam(auth()->user()->current_team_id)->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Payment $payment)
    {
        if (!$payment->isEditable()) {
            return redirect()->back()->with('error', 'Only pending payments can be updated.');
        }

        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'payment_type' => 'required|string',
            'reference_number' => 'nullable|string',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'cashier_shift_id' => 'nullable|exists:cashier_shifts,id',
        ]);

        $payment->update($data);

        return redirect()->route('finance.payments.index')->with('success', 'Payment updated successfully.');
    }

    public function destroy(Payment $payment)
    {
        if ($payment->status === 'confirmed') {
            // Reversing logic instead of simple delete if confirmed?
            // User says: soft delete, reverse transaction
            $this->paymentService->reversePayment($payment, auth()->id(), 'Payment deleted');
        }

        $payment->delete();

        return redirect()->route('finance.payments.index')->with('success', 'Payment deleted successfully.');
    }

    public function confirm($id)
    {
        $payment = Payment::findOrFail($id);
        if ($payment->status !== 'pending') {
            return redirect()->back()->with('error', 'Only pending payments can be confirmed.');
        }

        $payment->confirm(auth()->id());
        $this->paymentService->processPayment($payment);

        return redirect()->back()->with('success', 'Payment confirmed successfully.');
    }

    public function cancel(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string']);
        
        $payment = Payment::findOrFail($id);
        $payment->cancel(auth()->id(), $request->reason);

        if ($payment->transaction_id) {
            $this->paymentService->reversePayment($payment, auth()->id(), $request->reason);
        }

        return redirect()->back()->with('success', 'Payment cancelled successfully.');
    }

    public function reverse(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string']);
        
        $payment = Payment::findOrFail($id);
        $this->paymentService->reversePayment($payment, auth()->id(), $request->reason);

        return redirect()->back()->with('success', 'Payment reversed successfully.');
    }

    public function print($id)
    {
        $payment = Payment::with(['guest', 'reservation', 'team'])->findOrFail($id);
        return view('print.payment-receipt', compact('payment'));
    }

    public function export(Request $request)
    {
        // Simple CSV export for demonstration or use a package
        // This is a placeholder for the real export logic
        return redirect()->back()->with('success', 'Export started.');
    }
}
