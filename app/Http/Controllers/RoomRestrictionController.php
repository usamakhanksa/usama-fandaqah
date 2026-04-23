<?php

namespace App\Http\Controllers;

use App\Models\RoomRestriction;
use App\Models\Room;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RoomRestrictionController extends Controller {
    public function index(Request $request) {
        return Inertia::render('Inventory/Restrictions', [
            'restrictions' => RoomRestriction::with('room')->paginate(15),
            'rooms' => Room::select('id', 'room_number')->get()
        ]);
    }

    public function store(Request $request) {
        $data = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'restriction_type' => 'required|in:cta,ctd,min_los,max_los,closed',
            'value' => 'nullable|string',
            'reason' => 'nullable|string'
        ]);
        RoomRestriction::create($data);
        return redirect()->back()->with('success', 'Restriction applied.');
    }

    public function destroy(RoomRestriction $restriction) {
        $restriction->delete();
        return redirect()->back()->with('success', 'Restriction removed.');
    }
}
