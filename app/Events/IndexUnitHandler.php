<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class IndexUnitHandler
{
    use SerializesModels;


    public $key;
    public $data;
    public $team_id;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($key , $data , $team_id)
    {
        $this->key = $key;
        $this->data = $data;
        $this->team_id = $team_id;
    }

}
