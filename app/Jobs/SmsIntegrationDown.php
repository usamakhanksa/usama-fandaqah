<?php

namespace App\Jobs;

use App\Team;
use Illuminate\Bus\Queueable;
use App\Mail\SMSIntegrationDownMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SmsIntegrationDown implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public $tries = 3;

    private $event;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($event)
    {
        $this->event = $event;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $emails = [
            'info@fandaqah.com',
            'ealabd@sure.com.sa',
            'irashad@sure.com.sa',
            'kaghannam@sure.com.sa'
        ];
        $subject = " يوجد عطل في مقدم خدمة الرسائل "   . $this->event->data->type ;
        $team = Team::find($this->event->data->team_id);
        $data = [
            'to' => $emails,
            'subject' => $subject,
            'html' => view('email.sms_integration_down')
            ->with([
                'team_name' =>  $team->name,
                'credentials' =>  json_encode($this->event->data->payload['credentials']),
                'type' =>  $this->event->data->type
            ])->render(),
        ];
        $send = sendMailUsingMailMicroservice($data);

        // Mail::to($emails)->send(new SMSIntegrationDownMail($this->event->data->team_id, $this->event->data->payload['credentials'] , $this->event->data->type));
    }
}
