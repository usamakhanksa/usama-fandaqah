<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use App\Models\VoucherRedemption;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class VoucherController extends Controller
{
    public function index()
    {
        $vouchers = Voucher::paginate(10);
        return Inertia::render('Marketing/Vouchers/Index', [
            'vouchers' => $vouchers
        ]);
    }

    public function create()
    {
        return Inertia::render('Marketing/Vouchers/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'voucher_type' => 'required|in:gift,credit,service,stay,dining',
            'value' => 'required|numeric|min:0',
            'valid_from' => 'required|date',
            'valid_to' => 'required|date|after:valid_from',
        ]);

        $validated['team_id'] = currentTeam()->id;
        $validated['code'] = strtoupper(Str::random(10));
        $validated['initial_value'] = $request->value;
        $validated['remaining_value'] = $request->value;
        $validated['created_by'] = Auth::id();

        Voucher::create($validated);

        return redirect()->route('vouchers.index')->with('success', 'Voucher generated: ' . $validated['code']);
    }

    public function redeem(Request $request, Voucher $voucher)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $voucher->remaining_value,
            'reservation_id' => 'nullable|exists:reservations,id',
            'notes' => 'nullable|string'
        ]);

        if ($voucher->status !== 'active' && $voucher->status !== 'partially_redeemed') {
            return back()->with('error', 'Voucher is not active.');
        }

        $voucher->remaining_value -= $request->amount;
        $voucher->status = $voucher->remaining_value <= 0 ? 'redeemed' : 'partially_redeemed';
        if ($voucher->remaining_value <= 0) {
            $voucher->redeemed_at = now();
            $voucher->redeemed_by = Auth::id();
        }
        $voucher->save();

        VoucherRedemption::create([
            'voucher_id' => $voucher->id,
            'team_id' => $voucher->team_id,
            'amount' => $request->amount,
            'reservation_id' => $request->reservation_id,
            'redeemed_by' => Auth::id(),
            'notes' => $request->notes
        ]);

        return back()->with('success', 'Voucher redeemed successfully.');
    }
}
