<?php

namespace App\Http\Controllers;

use App\Models\HousekeepingTask;
use App\Models\Room;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HousekeepingController extends Controller {
    public function index(Request $request) {
        $tasks = HousekeepingTask::with('room')
            ->when($request->search, fn($q, $s) => $q->whereHas('room', fn($r) => $r->where('room_number', 'like', "%{$s}%")))
            ->orderByRaw("FIELD(status, 'pending', 'in_progress', 'completed')")
            ->orderBy('scheduled_at')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Inventory/Housekeeping', [
            'tasks' => $tasks,
            'rooms' => Room::select('id', 'room_number')->get(),
            'filters' => $request->only(['search'])
        ]);
    }

    public function store(Request $request) {
        $data = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'assigned_to' => 'nullable|string',
            'task_type' => 'required|in:daily_refresh,deep_clean,inspection,maintenance',
            'status' => 'required|in:pending,in_progress,completed',
            'notes' => 'nullable|string'
        ]);
        HousekeepingTask::create($data);
        return redirect()->back()->with('success', 'Task assigned.');
    }

    public function update(Request $request, HousekeepingTask $housekeeping) {
        $housekeeping->update($request->all());
        
        // Sync Room Status
        if ($housekeeping->status === 'completed' && $housekeeping->task_type === 'deep_clean') {
            $housekeeping->room->update(['status' => 'clean']);
        }
        
        return redirect()->back()->with('success', 'Task updated.');
    }

    public function destroy(HousekeepingTask $housekeeping) {
        $housekeeping->delete();
        return redirect()->back()->with('success', 'Task deleted.');
    }
}
