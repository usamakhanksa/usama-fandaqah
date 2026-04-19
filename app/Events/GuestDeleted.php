<?php

namespace App\Events;

use App\Handlers\Settings;
use App\Integration\SHMS;
use App\Jobs\SHMS\CheckOutGuest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GuestDeleted 
{
    use SerializesModels;

    public $guest;
    public $team_id;

    /**
     * Create a new event instance.
     *
     * @param Reservation $guest
     * @return guest
     */
    public function __construct($guest, $team_id)
    {
        $this->guest = $guest;
        $this->team_id = $team_id;
        $this->deleteGuestSHMS();
    }

    protected function deleteGuestSHMS()
    {
        $credentials = Settings::checkIntegration('SHMS', $this->team_id);
        if ($credentials && isset($this->guest->shomoos_id)) {
            // CheckOutGuest::dispatch($this->guest, $credentials);
            SHMS::checkOutGuest($this->guest, $credentials);
        }
    }

}
