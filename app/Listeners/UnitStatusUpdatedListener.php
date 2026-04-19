<?php

namespace App\Listeners;

use App\Handlers\Settings;
use App\Jobs\SCTH\OccupancyUpdate;
use App\Jobs\SCTH\UpdateBooking;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UnitStatusUpdatedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $this->checkSCTH($event->unit);
    }

    protected function checkSCTH($unit)
    {
        $credentials = Settings::checkIntegration('SCTH', $unit->team_id);
        if ($credentials) {
            OccupancyUpdate::dispatch($unit->team, $credentials, false);
        }
    }
}
