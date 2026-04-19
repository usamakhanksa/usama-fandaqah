<?php

namespace App\Broadcasting;

use App\Integration;
use App\Notifications\DailyBriefReport;
use App\User;
use Illuminate\Notifications\Notification;
use Liliom\Unifonic\UnifonicFacade;

class SMSChannel
{
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toSms($notifiable);
    }
}
