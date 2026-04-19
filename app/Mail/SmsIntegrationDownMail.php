<?php

namespace App\Mail;

use App\Team;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SmsIntegrationDownMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    private $team_id;
    private $credentials;
    private $type;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($team_id , $credentials , $type)
    {
        $this->team_id = $team_id;
        $this->credentials = $credentials;
        $this->type = $type;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $team = Team::find($this->team_id);
        $subject = " يوجد عطل في مقدم خدمة الرسائل "   . $this->type ;
        return $this
            ->subject($subject)
            ->view('email.sms_integration_down')
            ->with([
                'team_name' =>  $team->name,
                'credentials' =>  json_encode($this->credentials),
                'type' =>  $this->type
            ]);
    }
}
