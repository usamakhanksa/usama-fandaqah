<?php

namespace App\Mail\Owner;

use App\Activity;
use App\OnlineReservation;
use App\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ReservationCanceled extends Mailable implements ShouldQueue
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
                                ->where('description' , 'LIKE' , '%' . 'إلغاء الحجز' . '%')
                                ->first();


        return $this
            ->view('email.owner.reservation_canceled')
            ->with(['reservation'   =>  $this->reservation , 'logObj' => $logObj])
            ->subject("إلغاء الحجز رقم $number - $nights ليالي")
            ;
    }
}
