<?php

namespace App\Events;

use App\Guest;
use App\Handlers\Settings;
use App\Integration\SHMS;
use App\Jobs\SHMS\DeleteEscort;
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

class ShomoosDeleteEscort 
{
    use SerializesModels;

    public $escort;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Guest $escort)
    {
        $this->escort = $escort;
        $this->checkSHMS();
    }

    protected function checkSHMS()
    {
        $credentials = Settings::checkIntegration('SHMS', $this->escort->team_id);
        if ($credentials && $this->escort->shomoos_escort_id) {
            SHMS::deleteEscort($this->escort,$credentials);
        }

    }

}
