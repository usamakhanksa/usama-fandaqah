<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReservationAuditLock;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReservationAuditLockController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $team = $request->user()->currentTeam;
        if (!$team) {
            return response()->json(['data' => []]);
        }

        $query = ReservationAuditLock::where('team_id', $team->id)
            ->with(['reservation', 'auditLog'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        if ($request->filled('reason')) {
            $query->whereHas('auditLog', function ($q) use ($request) {
                $q->where('notes', 'like', '%' . $request->reason . '%');
            });
        }

        return response()->json($query->paginate($request->input('per_page', 15)));
    }

    public function show($id): JsonResponse
    {
        $lock = ReservationAuditLock::with(['reservation', 'auditLog'])
            ->where('reservation_id', $id)
            ->firstOrFail();

        return response()->json(['data' => $lock]);
    }
}
