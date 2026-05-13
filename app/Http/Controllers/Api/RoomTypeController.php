<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RoomTypeController extends Controller
{
    private function teamId(Request $request): int
    {
        return $request->user()->currentTeam->id;
    }

    public function index(Request $request): JsonResponse
    {
        $query = RoomType::where('team_id', $this->teamId($request));

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        return response()->json($query->orderBy('name')->paginate($request->input('per_page', 20)));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'base_price' => 'nullable|numeric|min:0',
        ]);

        $roomType = RoomType::create(array_merge($validated, ['team_id' => $this->teamId($request)]));

        return response()->json(['message' => 'Room type created', 'data' => $roomType], 201);
    }

    public function update(Request $request, RoomType $roomType): JsonResponse
    {
        if ($roomType->team_id !== $this->teamId($request)) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'base_price' => 'nullable|numeric|min:0',
        ]);

        $roomType->update($validated);

        return response()->json(['message' => 'Room type updated', 'data' => $roomType->fresh()]);
    }

    public function destroy(Request $request, RoomType $roomType): JsonResponse
    {
        if ($roomType->team_id !== $this->teamId($request)) {
            abort(404);
        }

        $roomType->delete();

        return response()->json(['message' => 'Room type deleted']);
    }
}
