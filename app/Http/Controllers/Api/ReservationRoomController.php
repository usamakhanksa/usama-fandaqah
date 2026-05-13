<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReservationService;
use App\Http\Resources\ReservationResource;
use Illuminate\Http\Request;

class ReservationRoomController extends Controller
{
    protected $reservationService;

    public function __construct(ReservationService $reservationService)
    {
        $this->reservationService = $reservationService;
    }

    /**
     * List all rooms for a reservation.
     */
    public function index($reservationId)
    {
        $rooms = $this->reservationService->getReservationRooms($reservationId);
        return ReservationResource::collection($rooms);
    }

    /**
     * Add a room to a reservation.
     */
    public function store(Request $request, $reservationId)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
        ]);

        try {
            $reservation = $this->reservationService->addRoomToReservation($reservationId, $validated['room_id']);
            return new ReservationResource($reservation);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Remove a room from a reservation.
     */
    public function destroy($reservationId, $subReservationId)
    {
        try {
            $this->reservationService->removeRoomFromReservation($reservationId, $subReservationId);
            return response()->json(['message' => 'Room removed successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
