<?php

namespace App\Services;

use App\Models\RoomStatusLog;
use App\Models\Unit;
use App\Models\UnitStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class RoomStatusService
{
    /**
     * Log a status change for a unit.
     */
    public function logStatusChange(Unit $unit, string $toStatus, ?string $reason = null, ?Model $reference = null)
    {
        $fromStatus = $unit->status; // Current status before change

        // Only log if the status actually changed
        if ($fromStatus === $toStatus) {
            return;
        }

        RoomStatusLog::create([
            'unit_id' => $unit->id,
            'team_id' => $unit->team_id,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'changed_by' => Auth::id(),
            'change_reason' => $reason,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference ? $reference->id : null,
            'changed_at' => now(),
        ]);

        // Update the unit status if needed (some callers might update it themselves)
        if ($unit->status !== $toStatus) {
            $unit->update(['status' => $toStatus]);
            
            // Also update unit_status_id if a corresponding UnitStatus exists
            $statusModel = UnitStatus::where('name', $toStatus)->first();
            if ($statusModel) {
                $unit->update(['unit_status_id' => $statusModel->id]);
            }
        }
    }
}
