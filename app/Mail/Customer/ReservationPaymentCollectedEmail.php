<?php

namespace App\Mail\Customer;

use App\OnlineReservation;
use App\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ReservationPaymentCollectedEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $tries = 1;

    public $reservation_id;
    public $amount;
    public $transaction_meta;
    public $lang;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($reservation_id,$amount,$transaction_meta,$lang)
    {
        $this->reservation_id = $reservation_id;
        $this->amount = $amount;
        $this->transaction_meta = $transaction_meta;
        $this->lang = $lang;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        
        $reservation = Reservation::with(['team','unit','customer'])->find($this->reservation_id);
        return $this
            ->view('email.customer.reservation_payment_collected')
            ->with(['reservation'   =>  $reservation , 'amount' => $this->amount , 'transaction_meta' => $this->transaction_meta , 'lang' => $this->lang])
            ->subject(__('Payment Receipt For Reservation : :number',['number' => $reservation->number],$this->lang));
    }
}
