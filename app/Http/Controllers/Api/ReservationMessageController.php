<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReservationMessageLog;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReservationMessageController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $team = $request->user()->currentTeam;
        if (!$team) {
            return response()->json(['data' => []]);
        }

        $query = ReservationMessageLog::where('team_id', $team->id)
            ->with(['sentBy'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->paginate($request->input('per_page', 15)));
    }

    public function show($id): JsonResponse
    {
        $message = ReservationMessageLog::findOrFail($id);

        return response()->json(['data' => $message]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'type'           => 'required|in:sms,email,whatsapp',
            'subject'        => 'nullable|string|max:255',
            'message'        => 'required|string',
        ]);

        $team = $request->user()->currentTeam;

        $log = ReservationMessageLog::create([
            'team_id'        => $team->id,
            'reservation_id' => $validated['reservation_id'],
            'type'           => $validated['type'],
            'subject'        => $validated['subject'] ?? null,
            'message'        => $validated['message'],
            'status'         => 'sent',
            'sent_by'        => $request->user()->id,
            'sent_at'        => now(),
        ]);

        return response()->json(['message' => 'Message sent', 'data' => $log], 201);
    }
}
