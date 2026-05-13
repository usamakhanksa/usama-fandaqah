<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\BlockedGuest;
use App\Models\Guest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class BlockedGuestController extends Controller
{
    public function index(Request $request)
    {
        $blockedGuests = BlockedGuest::with(['guest', 'blocker'])
            ->when($request->search, function ($query, $search) {
                $query->whereHas('guest', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('id_number', 'like', "%{$search}%");
                });
            })
            ->paginate(15);

        return Inertia::render('Guests/Blocked/Index', [
            'blockedGuests' => $blockedGuests,
            'filters' => $request->only(['search'])
        ]);
    }

    public function block(Request $request)
    {
        $request->validate([
            'guest_id' => 'required|exists:guests,id',
            'reason' => 'required|string',
            'severity' => 'required|in:warning,do_not_rent,blacklisted',
            'notes' => 'nullable|string'
        ]);

        BlockedGuest::updateOrCreate(
            ['guest_id' => $request->guest_id, 'is_active' => true],
            [
                'team_id' => currentTeam()->id,
                'reason' => $request->reason,
                'blocked_by' => Auth::id(),
                'blocked_at' => now(),
                'severity' => $request->severity,
                'notes' => $request->notes,
                'is_active' => true
            ]
        );

        return back()->with('success', 'Guest has been blocked.');
    }

    public function unblock(Request $request, BlockedGuest $blockedGuest)
    {
        $request->validate([
            'unblock_reason' => 'required|string'
        ]);

        $blockedGuest->update([
            'is_active' => false,
            'unblocked_by' => Auth::id(),
            'unblocked_at' => now(),
            'unblock_reason' => $request->unblock_reason
        ]);

        return back()->with('success', 'Guest has been unblocked.');
    }

    public function show(BlockedGuest $blockedGuest)
    {
        $blockedGuest->load(['guest', 'blocker', 'unblocker']);
        return Inertia::render('Guests/Blocked/Show', [
            'blockedGuest' => $blockedGuest
        ]);
    }

    public function export()
    {
        // Implementation for export
        return back()->with('info', 'Export feature coming soon.');
    }
}
