<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\DigitalSignature;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DigitalSignatureController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $team = $request->user()->currentTeam;
        if (!$team) {
            return response()->json(['data' => []]);
        }

        $query = DigitalSignature::where('team_id', $team->id)
            ->with(['user'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $signatures = $query->paginate($request->input('per_page', 15));

        return response()->json($signatures);
    }

    public function show($id): JsonResponse
    {
        $signature = DigitalSignature::with(['user'])->findOrFail($id);

        return response()->json(['data' => $signature]);
    }
}
