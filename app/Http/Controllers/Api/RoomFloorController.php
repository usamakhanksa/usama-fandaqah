<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RoomFloor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RoomFloorController extends Controller
{
    private function teamId(Request $request): int
    {
        return $request->user()->currentTeam->id;
    }

    public function index(Request $request): JsonResponse
    {
        $query = RoomFloor::where('team_id', $this->teamId($request));

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        return response()->json($query->orderBy('level')->paginate($request->input('per_page', 20)));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'level' => 'required|integer|min:0',
        ]);

        $floor = RoomFloor::create(array_merge($validated, ['team_id' => $this->teamId($request)]));

        return response()->json(['message' => 'Room floor created', 'data' => $floor], 201);
    }

    public function update(Request $request, RoomFloor $roomFloor): JsonResponse
    {
        if ($roomFloor->team_id !== $this->teamId($request)) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'level' => 'required|integer|min:0',
        ]);

        $roomFloor->update($validated);

        return response()->json(['message' => 'Room floor updated', 'data' => $roomFloor->fresh()]);
    }

    public function destroy(Request $request, RoomFloor $roomFloor): JsonResponse
    {
        if ($roomFloor->team_id !== $this->teamId($request)) {
            abort(404);
        }

        $roomFloor->delete();

        return response()->json(['message' => 'Room floor deleted']);
    }
}
