<?php

namespace App\Mail\Owner;

use App\Activity;
use App\OnlineReservation;
use App\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ReservationDeleted extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /** @var OnlineReservation */
    private $reservation;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $nights = $this->reservation->nights;
        $number = $this->reservation->number;

        $logObj = Activity::where('team_id' , $this->reservation->team_id)
            ->where('subject_id' , $this->reservation->id)
            // ->where('causer_id' , auth()->user()->id)
            ->where('description' , 'LIKE' , '%' . 'حذف الحجز' . '%')
            ->first();


        return $this
            ->view('email.owner.reservation_deleted')
            ->with(['reservation'   =>  $this->reservation , 'logObj' => $logObj])
            ->subject("حذف الحجز رقم $number - $nights ليالي")
            ;
    }
}
