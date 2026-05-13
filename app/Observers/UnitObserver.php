<?php

namespace App\Observers;

use App\Models\RoomStatusLog;
use App\Models\Unit;
use Illuminate\Support\Facades\Auth;

class UnitObserver
{
    /**
     * Handle the Unit "updated" event.
     */
    public function updated(Unit $unit): void
    {
        if ($unit->wasChanged('status')) {
            $fromStatus = $unit->getOriginal('status');
            $toStatus = $unit->status;

            // Log the manual change
            RoomStatusLog::create([
                'unit_id' => $unit->id,
                'team_id' => $unit->team_id,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'changed_by' => Auth::id(),
                'change_reason' => 'Manual change',
                'changed_at' => now(),
            ]);
        }
    }
}
