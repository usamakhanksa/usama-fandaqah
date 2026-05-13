<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Services\ReservationService;
use App\Http\Resources\ReservationResource;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;

class ReservationController extends Controller
{
    protected ReservationService $reservationService;

    public function __construct(ReservationService $reservationService)
    {
        $this->reservationService = $reservationService;
    }

    /**
     * Display a listing of reservations with advanced filters and search.
     */
    public function index(Request $request)
    {
        $filters = $request->only([
            'search', 'status', 'date_in', 'date_out', 
            'source_id', 'room_type_id', 'company_id',
            'sort_by', 'sort_order'
        ]);

        $perPage = $request->integer('per_page', 25);
        
        $reservations = $this->reservationService->getReservations($filters, $perPage);

        return ReservationResource::collection($reservations);
    }

    /**
     * Store a newly created reservation.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'guest_id' => 'nullable|exists:guests,id',
            'guest_data' => 'required_without:guest_id|array',
            'guest_data.name' => 'required_without:guest_id|string',
            'guest_data.email' => 'nullable|email',
            'guest_data.phone' => 'nullable|string',
            'room_id' => 'required|exists:rooms,id',
            'unit_id' => 'nullable|exists:units,id',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'category' => 'nullable|string|in:Normal,Complimentary,HouseUse,DayUse',
            'special_request' => 'nullable|string',
            'company_id' => 'nullable|exists:companies,id',
            'source_id' => 'nullable|exists:sources,id',
        ]);

        try {
            $reservation = $this->reservationService->createReservation($data);
            return new ReservationResource($reservation);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Display the specified reservation with all details.
     */
    public function show($id)
    {
        $reservation = Reservation::with([
            'guest', 'room.roomType', 'unit', 'company', 'source', 
            'createdBy', 'transactions', 'serviceLogs', 'booking.invoices'
        ])->findOrFail($id);

        return new ReservationResource($reservation);
    }

    /**
     * Update the specified reservation.
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'room_id' => 'sometimes|exists:rooms,id',
            'check_in' => 'sometimes|date',
            'check_out' => 'sometimes|date|after:check_in',
            'status' => 'sometimes|string',
            'special_request' => 'sometimes|nullable|string',
        ]);

        try {
            $reservation = $this->reservationService->updateReservation($id, $data);
            return new ReservationResource($reservation);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Cancel/Soft delete a reservation.
     */
    public function destroy(Request $request, $id)
    {
        $reason = $request->string('cancellation_reason', 'Cancelled by user');
        try {
            $this->reservationService->cancel($id, $reason);
            return response()->json(['message' => __('Reservation cancelled successfully')]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Mark reservation as Checked In.
     */
    public function checkIn($id)
    {
        try {
            $reservation = $this->reservationService->checkIn($id);
            return new ReservationResource($reservation);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Mark reservation as Checked Out.
     */
    public function checkOut($id)
    {
        try {
            $reservation = $this->reservationService->checkOut($id);
            return new ReservationResource($reservation);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Get reservations departing today.
     */
    public function departures(Request $request)
    {
        $departures = $this->reservationService->getDepartures($request->all());
        return ReservationResource::collection($departures);
    }

    /**
     * Get reservations arriving today.
     */
    public function arrivals(Request $request)
    {
        $arrivals = $this->reservationService->getArrivals($request->all());
        return ReservationResource::collection($arrivals);
    }

    /**
     * Get reservations currently in-house.
     */
    public function inHouse(Request $request)
    {
        $inHouse = $this->reservationService->getInHouseGuests($request->all());
        return ReservationResource::collection($inHouse);
    }

    /**
     * Get online reservations.
     */
    public function online(Request $request)
    {
        $reservations = $this->reservationService->getOnlineReservations($request->all());
        return ReservationResource::collection($reservations);
    }

    /**
     * Confirm a pending online reservation.
     */
    public function confirm($id)
    {
        try {
            $reservation = $this->reservationService->confirm($id);
            return new ReservationResource($reservation);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Reject/Cancel an online reservation.
     */
    public function reject(Request $request, $id)
    {
        $reason = $request->string('reason', 'Rejected by staff');
        try {
            $reservation = $this->reservationService->reject($id, $reason);
            return new ReservationResource($reservation);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Get reservation calendar data.
     */
    public function calendar(Request $request)
    {
        $data = $this->reservationService->getCalendarGrid($request->all());
        return response()->json(['data' => $data]);
    }

    /**
     * Store a newly created reservation using the quick create form.
     */
    public function quickStore(Request $request)
    {
        $data = $request->validate([
            'guest_name' => 'required|string',
            'phone' => 'required|string',
            'email' => 'nullable|email',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'room_type_id' => 'required|exists:room_types,id',
            'source_id' => 'nullable|exists:sources,id',
        ]);

        try {
            $reservation = $this->reservationService->quickCreateReservation($data);
            return new ReservationResource($reservation);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Export reservations to CSV.
     */
    public function export(Request $request)
    {
        $filters = $request->all();
        $reservations = $this->reservationService->getReservations($filters, 1000); // Higher limit for export

        $csvData = "Reservation #,Guest Name,Room,Check In,Check Out,Status,Total\n";
        foreach ($reservations as $res) {
            $csvData .= "{$res->code},{$res->guest?->name},{$res->room?->number},{$res->check_in?->toDateString()},{$res->check_out?->toDateString()},{$res->status},{$res->booking?->total_amount}\n";
        }

        return Response::make($csvData, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="reservations_' . date('Y-m-d') . '.csv"',
        ]);
    }

    /**
     * Get OTA reservations.
     */
    public function ota(Request $request)
    {
        $reservations = $this->reservationService->getOTAReservations($request->all());
        return ReservationResource::collection($reservations);
    }

    /**
     * Sync OTA reservation status.
     */
    public function syncStatus($id)
    {
        try {
            $reservation = $this->reservationService->syncOTAStatus($id);
            return new ReservationResource($reservation);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Get group reservations.
     */
    public function groupIndex(Request $request)
    {
        $groups = $this->reservationService->getGroupReservations($request->all());
        return response()->json($groups);
    }

    /**
     * Create group reservation.
     */
    public function groupStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'company_id' => 'nullable|exists:companies,id',
            'room_ids' => 'required|array',
            'room_ids.*' => 'exists:rooms,id',
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'rate' => 'nullable|numeric',
            'instructions' => 'nullable|string',
        ]);

        try {
            $group = $this->reservationService->createGroupReservation($validated);
            return response()->json($group, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Cancel group reservation.
     */
    public function groupCancel($id)
    {
        try {
            $group = $this->reservationService->cancelGroupReservation($id);
            return response()->json($group);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Get reservation ratings.
     */
    public function ratings(Request $request)
    {
        $teamId = $request->user()->current_team_id;
        $query = \App\Models\ReservationRating::with(['reservation.guest'])
            ->whereHas('reservation', fn($q) => $q->where('team_id', $teamId));

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        return response()->json($query->latest()->paginate(25));
    }

    /**
     * Show a single reservation rating.
     */
    public function ratingShow($id)
    {
        $rating = \App\Models\ReservationRating::with(['reservation.guest'])->findOrFail($id);
        return response()->json($rating);
    }
}