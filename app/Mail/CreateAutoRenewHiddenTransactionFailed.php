<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CreateAutoRenewHiddenTransactionFailed extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $tries = 2;

    public $reservation;
    public $exception_message;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($reservation,$exception_message)
    {
        $this->reservation = $reservation;
        $this->exception_message = $exception_message;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('email.hidden_auto_renew_transaction_failed')
                ->from(env('MAIL_FROM_ADDRESS') , 'Fandaqah Hidden Auto Renew Transaction Failure')
                ->with(['reservation' => $this->reservation ,  'exception_message' => $this->exception_message]);
    }
}
