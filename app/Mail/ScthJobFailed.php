<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ScthJobFailed extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $tries = 2;

    public $team;
    public $exception_message;
    public $failed_at;
    public $coming_from;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($team,$exception_message,$failed_at,$coming_from = null)
    {
        $this->team = $team;
        $this->exception_message = $exception_message;
        $this->failed_at = $failed_at;
        $this->coming_from = $coming_from;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('email.scth.job_failed')
                ->from(env('MAIL_FROM_ADDRESS') , 'Fandaqah Job Failure Detector')
                ->with(['team' => $this->team , 'exception_message' => $this->exception_message , 'failed_at' => $this->failed_at , 'coming_from' => $this->coming_from]);
    }
}
