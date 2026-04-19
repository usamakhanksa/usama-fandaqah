<?php

namespace App\Notifications;

use App\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Liliom\Unifonic\UnifonicFacade;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class CustomeNotification extends Notification
{
    use Queueable;

    public $team;
    private $subject;
    private $message;
    private $to;

    /**
     * CustomeNotification constructor.
     * @param String $message
     * @param string|null $subject
     */
    public function __construct(Reservation $reservation, array $to, String $message, ?string $subject = null)
    {
        $this->to = $to;
        $this->message = $message;
        $this->subject = $subject;
        $this->team = $reservation->team;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return $this->to;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        // return (new MailMessage)->subject($this->subject)->view(
        //     'email.reservation_mail',
        //     ['messageLine' => $this->message, 'team_name' => $this->team->name]
        // )
        //     ->from($this->team->email)
        //     ->replyTo($this->team->email, ucfirst($this->team->name));


        
    }

    public function toSms($notifiable)
    {
        $phone = preg_replace('/\s+/', '', $notifiable->phone);
        $phone = preg_replace('/[^0-9\-]/', '', $phone);

        $message = $this->message;

        sendSms($this->team->id, $message, $phone);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
