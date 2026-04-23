<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Http\Requests\SaveRoomRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RoomController extends Controller {
    public function index(Request $request) {
        $filters = $request->only(['search', 'status']);
        
        $rooms = Room::query()
            ->when($filters['search'] ?? null, fn($q, $s) => $q->search($s))
            ->when($filters['status'] ?? null, fn($q, $s) => $q->where('status', $s))
            ->orderBy('room_number')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Inventory/Rooms', [
            'rooms' => $rooms,
            'filters' => $filters,
            'stats' => [
                'total' => Room::count(),
                'dirty' => Room::where('status', 'dirty')->count(),
                'out_of_order' => Room::where('status', 'out_of_order')->count(),
            ]
        ]);
    }

    public function store(SaveRoomRequest $request) {
        Room::create($request->validated());
        return redirect()->back()->with('success', 'Room added successfully.');
    }

    public function update(SaveRoomRequest $request, Room $room) {
        $room->update($request->validated());
        return redirect()->back()->with('success', 'Room updated successfully.');
    }

    public function destroy(Room $room) {
        $room->delete();
        return redirect()->back()->with('success', 'Room deleted.');
    }
}
