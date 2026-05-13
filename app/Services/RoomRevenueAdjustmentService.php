<?php

namespace App\Services;

use App\Reservation;
use App\ServiceLog;
use App\NightAuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class RoomRevenueAdjustmentService
{
    /**
     * Post an adjustment for a closed business date.
     */
    public function postAdjustment(Reservation $reservation, array $data)
    {
        $targetDate = $data['business_date'];
        $amount = $data['amount'];
        $reason = $data['reason'];
        $type = $data['type']; // 'rebate' or 'charge'

        // 1. Verify target date is closed
        if (!$this->isDateClosed($reservation->team_id, $targetDate)) {
            throw new Exception("Adjustment can only be posted for closed business dates.");
        }

        // 2. Get current open business date
        $currentDate = $reservation->team->business_date;

        return DB::transaction(function () use ($reservation, $targetDate, $currentDate, $amount, $reason, $type) {
            // 3. Create ServiceLog entry
            // Note: We post it on the CURRENT business date, but link it to the TARGET date in meta
            $log = new ServiceLog();
            $log->reservation_id = $reservation->id;
            $log->team_id = $reservation->team_id;
            $log->service_id = $reservation->room_service_id; // Default room service
            $log->description = "Room Revenue Adjustment: " . ($type === 'rebate' ? '-' : '+') . " $amount ($reason) for $targetDate";
            $log->amount = $type === 'rebate' ? -$amount : $amount;
            $log->business_date = $currentDate; // Posted on current date
            $log->is_freezed = false;
            $log->meta = [
                'adjustment' => true,
                'original_business_date' => $targetDate,
                'reason' => $reason,
                'adjusted_by' => Auth::id(),
                'type' => $type
            ];
            $log->save();

            return $log;
        });
    }

    private function isDateClosed($teamId, $date)
    {
        return NightAuditLog::where('team_id', $teamId)
            ->where('business_date', $date)
            ->where('status', 'completed')
            ->exists();
    }
}
