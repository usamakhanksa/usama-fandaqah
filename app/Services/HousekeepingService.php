<?php

namespace App\Services;

use App\Models\HousekeepingTask;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HousekeepingService
{
    protected RoomStatusService $statusService;

    public function __construct(RoomStatusService $statusService)
    {
        $this->statusService = $statusService;
    }

    /**
     * Get the housekeeping board data.
     */
    public function getBoard($team)
    {
        return Unit::where('team_id', $team->id)
            ->with(['unitType', 'unitStatus'])
            ->get()
            ->groupBy('floor');
    }

    /**
     * Create a cleaning task for a unit.
     */
    public function createCleaningTask(Unit $unit, $assignedTo = null, $type = 'routine')
    {
        return HousekeepingTask::create([
            'team_id' => $unit->team_id,
            'unit_id' => $unit->id,
            'assigned_to' => $assignedTo,
            'type' => $type,
            'status' => 'pending',
            'created_by' => Auth::id(),
        ]);
    }

    /**
     * Start a cleaning task.
     */
    public function startTask(HousekeepingTask $task)
    {
        $task->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        $this->statusService->logStatusChange($task->unit, 'dirty', 'Cleaning in progress', $task);
    }

    /**
     * Complete a cleaning task.
     */
    public function completeTask(HousekeepingTask $task, $notes = null)
    {
        $task->update([
            'status' => 'completed',
            'completed_at' => now(),
            'notes' => $notes,
        ]);

        // After completion, room is clean but needs inspection
        $this->statusService->logStatusChange($task->unit, 'clean', 'Cleaning completed', $task);
    }

    /**
     * Inspect a unit.
     */
    public function inspectUnit(Unit $unit, $status = 'inspected', $notes = null)
    {
        $this->statusService->logStatusChange($unit, $status, $notes);
    }

    /**
     * Set room to dirty (e.g., after checkout or stay-over).
     */
    public function setDirty(Unit $unit, $reason = 'Checkout')
    {
        $this->statusService->logStatusChange($unit, 'dirty', $reason);
        $this->createCleaningTask($unit, null, $reason === 'Checkout' ? 'departure' : 'stay_over');
    }
}
