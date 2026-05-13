<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\Reservation;
use App\Models\Customer;
use App\Services\Guest\GuestService;
use App\Http\Requests\Guest\StoreGuestRequest;
use App\Http\Requests\Guest\UpdateGuestRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GuestController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Guest::class, 'guest');
    }

    public function index(Request $request)
    {
        $guests = Guest::with(['reservation', 'customer'])
            ->where('team_id', currentTeam()->id)
            ->paginate(20);
            
        return Inertia::render('Guests/Index', [
            'guests' => $guests
        ]);
    }

    public function create()
    {
        return Inertia::render('Guests/Create');
    }

    public function store(StoreGuestRequest $request)
    {
        $guest = GuestService::create($request->validated());
        
        return redirect()->route('guests.show', $guest)
            ->with('success', 'Guest created successfully.');
    }

    public function show(Guest $guest)
    {
        $guest->load(['reservation', 'customer', 'reservation.unit']);
        
        return Inertia::render('Guests/Show', [
            'guest' => $guest
        ]);
    }

    public function edit(Guest $guest)
    {
        return Inertia::render('Guests/Edit', [
            'guest' => $guest
        ]);
    }

    public function update(UpdateGuestRequest $request, Guest $guest)
    {
        $guest = GuestService::update($guest, $request->validated());
        
        return redirect()->route('guests.show', $guest)
            ->with('success', 'Guest updated successfully.');
    }

    public function destroy(Guest $guest)
    {
        $guest->delete();
        
        return redirect()->route('guests.index')
            ->with('success', 'Guest deleted successfully.');
    }

    public function verifyShomoos(Request $request, Guest $guest)
    {
        $this->authorize('verify', $guest);
        
        $verified = GuestService::verifyShomoos($guest, $request->id_number);
        
        return response()->json([
            'verified' => $verified,
            'message' => $verified ? 'Shomoos verification successful.' : 'Shomoos verification failed.'
        ]);
    }
}
