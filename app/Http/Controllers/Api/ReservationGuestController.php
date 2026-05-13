<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReservationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservationGuestController extends Controller
{
    protected $reservationService;

    public function __construct(ReservationService $reservationService)
    {
        $this->reservationService = $reservationService;
    }

    /**
     * List all guests for a reservation.
     */
    public function index($reservationId)
    {
        $guests = $this->reservationService->getReservationGuests($reservationId);
        return response()->json($guests);
    }

    /**
     * Add a guest to a reservation.
     */
    public function store(Request $request, $reservationId)
    {
        $validated = $request->validate([
            'guest_id' => 'required|exists:guests,id',
            'is_primary' => 'boolean',
            'relation' => 'nullable|string',
        ]);

        try {
            $this->reservationService->addGuestToReservation($reservationId, $validated['guest_id'], $validated);
            return response()->json(['message' => 'Guest added successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Remove a guest from a reservation.
     */
    public function destroy($reservationId, $guestId)
    {
        try {
            $this->reservationService->removeGuestFromReservation($reservationId, $guestId);
            return response()->json(['message' => 'Guest removed successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
