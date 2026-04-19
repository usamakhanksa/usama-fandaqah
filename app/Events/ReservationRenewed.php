<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ReservationRenewed
{
    use SerializesModels;


    public $id;

    public $amount;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($id,$amount)
    {
        $this->id = $id ;
        $this->amount = $amount;
    }

}
