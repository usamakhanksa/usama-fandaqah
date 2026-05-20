<?php

namespace App\Services;

use App\Models\MaintenanceTicket;
use App\Models\Unit;
use Illuminate\Support\Facades\Auth;

class MaintenanceService
{
    protected RoomStatusService $statusService;

    public function __construct(RoomStatusService $statusService)
    {
        $this->statusService = $statusService;
    }

    /**
     * Create a maintenance ticket.
     */
    public function createTicket(Unit $unit, array $data)
    {
        $ticket = MaintenanceTicket::create(array_merge($data, [
            'team_id' => $unit->team_id,
            'unit_id' => $unit->id,
            'status' => 'open',
            'created_by' => Auth::id(),
        ]));

        if ($data['priority'] === 'emergency' || ($data['is_out_of_order'] ?? false)) {
            $this->statusService->logStatusChange($unit, 'out-of-order', 'Maintenance: ' . $data['title'], $ticket);
        }

        return $ticket;
    }

    /**
     * Assign a ticket to a technician.
     */
    public function assignTicket(MaintenanceTicket $ticket, $userId)
    {
        $ticket->update([
            'assigned_to' => $userId,
            'status' => 'assigned',
        ]);
    }

    /**
     * Complete a maintenance ticket.
     */
    public function completeTicket(MaintenanceTicket $ticket, $notes = null)
    {
        $ticket->update([
            'status' => 'completed',
            'completed_at' => now(),
            'resolution_notes' => $notes,
        ]);

        // After completion, room might still be dirty
        $unit = $ticket->unit;
        if ($unit->status === 'out-of-order') {
            $this->statusService->logStatusChange($unit, 'dirty', 'Maintenance completed, needs cleaning', $ticket);
        }
    }
}
