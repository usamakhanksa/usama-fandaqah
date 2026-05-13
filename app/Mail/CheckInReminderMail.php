<?php

namespace App\Mail;

use App\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CheckInReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $reservation;
    public $customMessage;

    public function __construct(Reservation $reservation, $customMessage = null)
    {
        $this->reservation = $reservation;
        $this->customMessage = $customMessage;
    }

    public function build()
    {
        return $this->subject('Reservation Check-In Reminder - ' . $this->reservation->number)
                   ->markdown('emails.check_in_reminder');
    }
}