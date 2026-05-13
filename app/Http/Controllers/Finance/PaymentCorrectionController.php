<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentCorrection;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentCorrectionController extends Controller
{
    public function index()
    {
        $teamId = auth()->user()->current_team_id;
        $corrections = PaymentCorrection::where('team_id', $teamId)
            ->with(['originalPayment', 'creator', 'approver'])
            ->latest()
            ->paginate(15);

        return Inertia::render('Finance/PaymentCorrections/Index', [
            'corrections' => $corrections
        ]);
    }

    public function create(Request $request)
    {
        $payment = null;
        if ($request->has('payment_id')) {
            $payment = Payment::with('guest', 'reservation')->findOrFail($request->payment_id);
        }

        return Inertia::render('Finance/PaymentCorrections/Create', [
            'selectedPayment' => $payment
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'original_payment_id' => 'required|exists:payments,id',
            'correction_type' => 'required|in:amount_correction,method_correction,date_correction,account_correction,full_reversal',
            'corrected_values' => 'required|array',
            'reason' => 'required|string',
            'correction_date' => 'required|date',
        ]);

        $payment = Payment::findOrFail($request->original_payment_id);
        $teamId = auth()->user()->current_team_id;

        PaymentCorrection::create([
            'team_id' => $teamId,
            'original_payment_id' => $payment->id,
            'correction_type' => $validated['correction_type'],
            'original_values' => $payment->toArray(),
            'corrected_values' => $validated['corrected_values'],
            'reason' => $validated['reason'],
            'correction_date' => $validated['correction_date'],
            'status' => 'pending',
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('finance.payment-corrections.index')->with('success', 'Correction request submitted for approval.');
    }

    public function approve(PaymentCorrection $correction)
    {
        if ($correction->status !== 'pending') {
            return back()->with('error', 'Correction is not in pending state.');
        }

        $correction->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        // Logic to apply correction:
        // 1. Create reversal transaction
        // 2. If not full_reversal, create new corrected payment/transaction
        // $this->applyCorrection($correction);

        $correction->update(['status' => 'completed']);

        return back()->with('success', 'Correction approved and applied.');
    }

    public function reject(PaymentCorrection $correction)
    {
        if ($correction->status !== 'pending') {
            return back()->with('error', 'Correction is not in pending state.');
        }

        $correction->update(['status' => 'rejected']);

        return back()->with('success', 'Correction request rejected.');
    }

    public function show(PaymentCorrection $paymentCorrection)
    {
        return Inertia::render('Finance/PaymentCorrections/Show', [
            'correction' => $paymentCorrection->load(['originalPayment', 'creator', 'approver', 'reversalTransaction'])
        ]);
    }
}
