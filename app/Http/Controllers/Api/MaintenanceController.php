<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceTicket;
use App\Models\Unit;
use App\Services\MaintenanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class MaintenanceController extends Controller
{
    protected MaintenanceService $service;

    public function __construct(MaintenanceService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of tickets.
     */
    public function index(Request $request)
    {
        $tickets = MaintenanceTicket::with(['unit', 'creator', 'assignee'])
            ->where('team_id', $request->user()->currentTeam->id)
            ->orderBy('created_at', 'desc')
            ->get();
        return Response::json($tickets);
    }

    /**
     * Store a new ticket.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high,emergency',
            'is_out_of_order' => 'boolean',
        ]);

        $unit = Unit::findOrFail($data['unit_id']);
        $ticket = $this->service->createTicket($unit, $data);

        return Response::json($ticket, 201);
    }

    /**
     * Assign a ticket.
     */
    public function assign(Request $request, MaintenanceTicket $ticket)
    {
        $data = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $this->service->assignTicket($ticket, $data['assigned_to']);

        return Response::json(['message' => 'Ticket assigned']);
    }

    /**
     * Complete a ticket.
     */
    public function complete(Request $request, MaintenanceTicket $ticket)
    {
        $this->service->completeTicket($ticket, $request->input('notes'));
        return Response::json(['message' => 'Ticket completed']);
    }
}
