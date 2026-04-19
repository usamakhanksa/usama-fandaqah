<?php

namespace App\Events;

use App\Guest;
use App\Handlers\Settings;
use App\Integration\SHMS;
use App\Jobs\SHMS\InsertEscort;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class ShomoosUpdateEscort
{
    use Dispatchable, InteractsWithSockets, SerializesModels, Queueable;

    public $reservation_id;
    public $escort;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($reservation_id,Guest $escort)
    {
        $this->reservation_id = $reservation_id;
        $this->escort = $escort;
        $this->checkSHMS();
    }

    protected function checkSHMS()
    {
        $credentials = Settings::checkIntegration('SHMS', $this->escort->team_id);
        if ($credentials) {
            SHMS::updateEscort($this->escort,$credentials);
        }

    }

}
