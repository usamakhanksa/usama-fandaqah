<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ReservationExportMail extends Mailable
{
    use Queueable, SerializesModels;

    public $filePath;

    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    public function build()
    {
        return $this->subject('Reservation Export Data')
                   ->markdown('emails.reservation_export')
                   ->attach($this->filePath, [
                       'as' => 'reservations_export.xlsx',
                       'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                   ]);
    }
}