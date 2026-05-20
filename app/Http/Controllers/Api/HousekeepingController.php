<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HousekeepingTask;
use App\Models\Unit;
use App\Services\HousekeepingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class HousekeepingController extends Controller
{
    protected HousekeepingService $service;

    public function __construct(HousekeepingService $service)
    {
        $this->service = $service;
    }

    /**
     * Display the housekeeping board.
     */
    public function board(Request $request)
    {
        $board = $this->service->getBoard($request->user()->currentTeam);
        return Response::json($board);
    }

    /**
     * Display a listing of housekeeping tasks.
     */
    public function tasks(Request $request)
    {
        $tasks = HousekeepingTask::with(['unit', 'assignee'])
            ->where('team_id', $request->user()->currentTeam->id)
            ->orderBy('created_at', 'desc')
            ->get();
        return Response::json($tasks);
    }

    /**
     * Update a unit's status directly.
     */
    public function updateRoomStatus(Request $request, Unit $unit)
    {
        $data = $request->validate([
            'status' => 'required|string|in:dirty,clean,inspected,out-of-order',
            'notes' => 'nullable|string',
        ]);

        $this->service->inspectUnit($unit, $data['status'], $data['notes']);

        return Response::json(['message' => 'Room status updated successfully']);
    }

    /**
     * Start a task.
     */
    public function startTask(HousekeepingTask $task)
    {
        $this->service->startTask($task);
        return Response::json(['message' => 'Task started']);
    }

    /**
     * Complete a task.
     */
    public function completeTask(Request $request, HousekeepingTask $task)
    {
        $this->service->completeTask($task, $request->input('notes'));
        return Response::json(['message' => 'Task completed']);
    }
}
