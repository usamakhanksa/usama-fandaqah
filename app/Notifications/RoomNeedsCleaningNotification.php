<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\OneSignal\Exceptions\CouldNotSendNotification;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;
use NotificationChannels\OneSignal\OneSignalServiceProvider;
use NotificationChannels\OneSignal\OneSignalWebButton;
use Berkayk\OneSignal;

class RoomNeedsCleaningNotification extends Notification
{
    public $unit_name ;

    public function __construct($unit_name)
    {
        $this->unit_name = $unit_name;
    }

    public function via($notifiable)
    {
        return [OneSignalChannel::class];
    }

    public function toOneSignal($notifiable)
    {

        return OneSignalMessage::create()
            ->setSubject("تم تحويل حالة الوحدة  : {$this->unit_name} الى تحت التنظيف")
            ->setBody('اضغط للتحديث')
            ->setData('refresh' , true);
    }
}
