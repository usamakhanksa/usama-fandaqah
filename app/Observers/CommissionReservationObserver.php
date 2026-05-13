<?php

namespace App\Observers;

use App\Models\Reservation;
use App\Services\CommissionService;

class CommissionReservationObserver
{
    protected $commissionService;

    public function __construct(CommissionService $commissionService)
    {
        $this->commissionService = $commissionService;
    }

    /**
     * Handle the Reservation "created" event.
     */
    public function created(Reservation $reservation): void
    {
        $this->commissionService->processReservationCommission($reservation);
    }

    /**
     * Handle the Reservation "updated" event.
     */
    public function updated(Reservation $reservation): void
    {
        // Re-calculate if price or source changes
        if ($reservation->isDirty(['total_price', 'source_id', 'date_in', 'date_out'])) {
            $this->commissionService->processReservationCommission($reservation);
        }
    }
}
