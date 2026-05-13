<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\ReservationRepository;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReservationExtensionController extends Controller
{
    protected ReservationRepository $repository;

    public function __construct(ReservationRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(Request $request): JsonResponse
    {
        $team = $request->user()->currentTeam;
        if (!$team) {
            return response()->json(['data' => []]);
        }

        $filters = $request->only(['date', 'reservation_id', 'per_page']);
        $extensions = $this->repository->getExtensionHistory($team, $filters)
            ->paginate($filters['per_page'] ?? 15);

        return response()->json($extensions);
    }

    public function show($id): JsonResponse
    {
        $extension = \App\Models\ReservationStayExtension::with([
            'reservation.customer',
            'creator'
        ])->findOrFail($id);

        return response()->json(['data' => $extension]);
    }
}
