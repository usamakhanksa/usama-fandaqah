<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\ReservationRepository;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReservationTransferController extends Controller
{
    protected ReservationRepository $repository;

    public function __construct(ReservationRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of room transfers.
     */
    public function index(Request $request): JsonResponse
    {
        $team = $request->user()->currentTeam;
        if (!$team) {
            return response()->json(['data' => []]);
        }

        $filters = $request->only(['date', 'reservation_id', 'unit_id', 'per_page']);
        $transfers = $this->repository->getTransferHistory($team, $filters)
            ->paginate($filters['per_page'] ?? 15);

        return response()->json($transfers);
    }

    /**
     * Display the specified transfer details.
     */
    public function show($id): JsonResponse
    {
        $transfer = \App\Models\ReservationRoomTransfer::with([
            'reservation.customer',
            'fromUnit',
            'toUnit',
            'creator'
        ])->findOrFail($id);

        return response()->json(['data' => $transfer]);
    }
}
