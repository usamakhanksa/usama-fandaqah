<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ScthTenantDisconnected extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $tries = 2;

    public $integration;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($integration)
    {
        $this->integration = $integration;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('email.scth.tenant_disconnected')
                ->subject('Hotel Disconnected [fandaqah channel]')
                ->from(env('MAIL_FROM_ADDRESS') , 'Hotel Disconnected')
                ->with(['integration' => $this->integration]);
    }
}
