<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\PaidOut;
use App\Models\Reservation;
use App\Models\Guest;
use App\Services\Finance\TeamCounter; // Assuming this service exists from previous tasks
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaidOutController extends Controller
{
    public function index()
    {
        $teamId = auth()->user()->current_team_id;
        $paidOuts = PaidOut::where('team_id', $teamId)
            ->with(['reservation', 'guest', 'creator', 'approver'])
            ->latest()
            ->paginate(15);

        return Inertia::render('Finance/PaidOuts/Index', [
            'paidOuts' => $paidOuts
        ]);
    }

    public function create()
    {
        $teamId = auth()->user()->current_team_id;
        return Inertia::render('Finance/PaidOuts/Create', [
            'reservations' => Reservation::where('team_id', $teamId)->get(['id', 'number']),
            'guests' => Guest::where('team_id', $teamId)->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reservation_id' => 'nullable|exists:reservations,id',
            'guest_id' => 'nullable|exists:guests,id',
            'paid_out_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string',
            'category' => 'required|in:taxi,laundry,postage,courier,other',
            'vendor_name' => 'nullable|string',
            'receipt_number' => 'nullable|string',
            'payment_method' => 'required|in:cash,card',
        ]);

        $teamId = auth()->user()->current_team_id;
        
        // Use TeamCounter for unique number
        // Assuming TeamCounter::getNext('paid_out', $teamId, 'PO')
        $validated['paid_out_number'] = \App\Services\Finance\TeamCounter::getNext($teamId, 'paid_out', 'PO');
        $validated['team_id'] = $teamId;
        $validated['created_by'] = auth()->id();
        $validated['status'] = 'pending';

        PaidOut::create($validated);

        return redirect()->route('finance.paid-outs.index')->with('success', 'Paid-out request created.');
    }

    public function show(PaidOut $paidOut)
    {
        return Inertia::render('Finance/PaidOuts/Show', [
            'paidOut' => $paidOut->load(['reservation', 'guest', 'creator', 'approver', 'transaction'])
        ]);
    }

    public function approve(PaidOut $paidOut)
    {
        if ($paidOut->status !== 'pending') {
            return back()->with('error', 'Only pending requests can be approved.');
        }

        $paidOut->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        // Create transaction logic would go here
        // \App\Services\Finance\TransactionService::createFromPaidOut($paidOut);

        return back()->with('success', 'Paid-out request approved.');
    }

    public function reject(PaidOut $paidOut)
    {
        if ($paidOut->status !== 'pending') {
            return back()->with('error', 'Only pending requests can be rejected.');
        }

        $paidOut->update(['status' => 'rejected']);

        return back()->with('success', 'Paid-out request rejected.');
    }

    public function destroy(PaidOut $paidOut)
    {
        $paidOut->delete();
        return redirect()->route('finance.paid-outs.index')->with('success', 'Paid-out deleted.');
    }
}
