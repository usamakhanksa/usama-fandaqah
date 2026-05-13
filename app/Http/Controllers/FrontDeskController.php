<?php

namespace App\Http\Controllers;

use App\Services\FrontDeskService;
use App\Reservation;
use App\Guest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FrontDeskController extends Controller
{
    protected $frontDeskService;

    public function __construct(FrontDeskService $frontDeskService)
    {
        $this->frontDeskService = $frontDeskService;
    }

    /**
     * Process check-in for a reservation
     */
    public function checkIn(Request $request, $reservationId)
    {
        $data = $request->only([
            'check_in_time', 
            'digital_signature'
        ]);

        $result = $this->frontDeskService->processCheckIn($reservationId, $data);

        if ($result['success']) {
            return response()->json($result, 200);
        } else {
            return response()->json($result, 400);
        }
    }

    /**
     * Process check-out for a reservation
     */
    public function checkOut(Request $request, $reservationId)
    {
        $data = $request->only([
            'check_out_time', 
            'digital_signature'
        ]);

        $result = $this->frontDeskService->processCheckOut($reservationId, $data);

        if ($result['success']) {
            return response()->json($result, 200);
        } else {
            return response()->json($result, 400);
        }
    }

    /**
     * Add guest to reservation
     */
    public function addGuest(Request $request, $reservationId)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'id_number' => 'required|string|unique:guests,id_number',
            'id_type' => 'required|integer',
            'customer_type' => 'required|integer',
            'birthday_date' => 'nullable|date',
            'id_serial_number' => 'nullable|string',
            'visa_number' => 'nullable|string',
            'country_id' => 'nullable|integer',
            'relation_type' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->frontDeskService->addGuestToReservation($reservationId, $request->all());

        if ($result['success']) {
            return response()->json($result, 200);
        } else {
            return response()->json($result, 400);
        }
    }

    /**
     * Update guest information
     */
    public function updateGuest(Request $request, $guestId)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'id_number' => 'required|string',
            'id_type' => 'required|integer',
            'customer_type' => 'required|integer',
            'birthday_date' => 'nullable|date',
            'id_serial_number' => 'nullable|string',
            'visa_number' => 'nullable|string',
            'country_id' => 'nullable|integer',
            'relation_type' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->frontDeskService->updateGuest($guestId, $request->all());

        if ($result['success']) {
            return response()->json($result, 200);
        } else {
            return response()->json($result, 400);
        }
    }

    /**
     * Delete a guest
     */
    public function deleteGuest(Request $request, $guestId)
    {
        $result = $this->frontDeskService->deleteGuest($guestId);

        if ($result['success']) {
            return response()->json($result, 200);
        } else {
            return response()->json($result, 400);
        }
    }

    /**
     * Validate Shomoos ID
     */
    public function validateShomoosId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'shomoos_id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->frontDeskService->validateShomoosId($request->shomoos_id);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Assign a room to a reservation
     */
    public function assignRoom(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reservation_id' => 'required|integer|exists:reservations,id',
            'unit_id' => 'required|integer|exists:units,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->frontDeskService->assignRoom($request->reservation_id, $request->unit_id);

        if ($result['success']) {
            return response()->json($result, 200);
        } else {
            return response()->json($result, 400);
        }
    }

    /**
     * Create a walk-in booking
     */
    public function createWalkInBooking(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'unit_id' => 'required|integer|exists:units,id',
            'date_in' => 'required|date',
            'date_out' => 'required|date|after_or_equal:date_in',
            'customer.name' => 'required|string|max:255',
            'customer.phone' => 'required|string|max:255',
            'customer.email' => 'nullable|email|max:255',
            'customer.id_number' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->frontDeskService->createWalkInBooking($request->all());

        if ($result['success']) {
            return response()->json($result, 200);
        } else {
            return response()->json($result, 400);
        }
    }

    /**
     * Extend a reservation
     */
    public function extendReservation(Request $request, $reservationId)
    {
        $validator = Validator::make($request->all(), [
            'new_date_out' => 'required|date|after:date_out'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->frontDeskService->extendSameDay($reservationId, $request->all());

        if ($result['success']) {
            return response()->json($result, 200);
        } else {
            return response()->json($result, 400);
        }
    }

    /**
     * Handle no-show for a reservation
     */
    public function handleNoShow(Request $request, $reservationId)
    {
        $result = $this->frontDeskService->handleNoShow($reservationId);

        if ($result['success']) {
            return response()->json($result, 200);
        } else {
            return response()->json($result, 400);
        }
    }

    /**
     * Create an IPTV request
     */
    public function createIptvRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reservation_id' => 'required|integer|exists:reservations,id',
            'request_type' => 'required|string',
            'request_details' => 'nullable|string',
            'priority' => 'nullable|in:low,normal,high,urgent'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->frontDeskService->createIptvRequest($request->all());

        if ($result['success']) {
            return response()->json($result, 200);
        } else {
            return response()->json($result, 400);
        }
    }

    /**
     * Mark an IPTV request as treated
     */
    public function markIptvRequestAsTreated(Request $request, $requestId)
    {
        $result = $this->frontDeskService->markIptvRequestAsTreated($requestId, auth()->id());

        if ($result['success']) {
            return response()->json($result, 200);
        } else {
            return response()->json($result, 400);
        }
    }
}