<?php

namespace App\Events;

use App\Broadcasting\SMSChannel;
use App\Handlers\Settings;
use App\Jobs\SHMS\CheckOut;
use App\Notifications\ReservationRating;
use App\Reservation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReservationTransferred 
{
    use SerializesModels;

    /**
     * @var Reservation
     */
    public $reservation;

    /**
     * @var new_unit_number
     */
    public $new_unit_number;
    
    /**
     * @var old_unit_number
     */
    public $old_unit_number;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Reservation $reservation, $old_unit_number, $new_unit_number)
    {
        $this->reservation = $reservation;
        $this->new_unit_number = $new_unit_number;
        $this->old_unit_number = $old_unit_number;
    }

}
