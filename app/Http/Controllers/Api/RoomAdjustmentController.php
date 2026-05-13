<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\RoomAdjustmentRequest;
use App\Reservation;
use App\Services\RoomRevenueAdjustmentService;
use Illuminate\Http\Request;

class RoomAdjustmentController extends Controller
{
    protected $adjustmentService;

    public function __construct(RoomRevenueAdjustmentService $adjustmentService)
    {
        $this->adjustmentService = $adjustmentService;
    }

    /**
     * Store a new adjustment.
     */
    public function store(RoomAdjustmentRequest $request)
    {
        $reservation = Reservation::findOrFail($request->reservation_id);

        try {
            $log = $this->adjustmentService->postAdjustment($reservation, $request->validated());
            
            return response()->json([
                'message' => 'Adjustment posted successfully.',
                'data' => $log
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get adjustment history for a reservation.
     */
    public function index(Reservation $reservation)
    {
        $adjustments = $reservation->serviceLogs()
            ->where('meta->adjustment', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $adjustments
        ]);
    }
}
