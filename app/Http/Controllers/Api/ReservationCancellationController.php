<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReservationCancellationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $team = $request->user()->currentTeam;
        if (!$team) {
            return response()->json(['data' => []]);
        }

        $query = Reservation::where('team_id', $team->id)
            ->whereIn('status', ['cancelled', 'no_show'])
            ->orderBy('updated_at', 'desc');

        if ($request->filled('date')) {
            $query->whereDate('updated_at', $request->date);
        }

        if ($request->filled('type')) {
            $query->where('status', $request->type);
        }

        if ($request->filled('reason')) {
            $query->where('cancellation_reason', 'like', '%' . $request->reason . '%');
        }

        $total   = (clone $query)->count();
        $cancelled = (clone $query)->where('status', 'cancelled')->count();
        $noShow  = (clone $query)->where('status', 'no_show')->count();

        $results = $query->paginate($request->input('per_page', 15));

        $data = $results->toArray();
        $data['stats'] = [
            'total'     => $total,
            'cancelled' => $cancelled,
            'no_show'   => $noShow,
            'penalties' => $noShow, // no-shows may have penalty charges
        ];

        return response()->json($data);
    }

    public function show($id): JsonResponse
    {
        $reservation = Reservation::whereIn('status', ['cancelled', 'no_show'])
            ->findOrFail($id);

        return response()->json(['data' => $reservation]);
    }
}
