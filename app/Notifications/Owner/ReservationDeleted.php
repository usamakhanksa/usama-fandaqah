<?php

namespace App\Notifications\Owner;

use App\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Laravel\Spark\Notifications\SparkChannel;
use Laravel\Spark\Notifications\SparkNotification;

class ReservationDeleted extends Notification implements ShouldQueue
{
    use Queueable;

    /** @var Reservation */
    private $reservation;

    /** @var String */
    private $msg;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Reservation $reservation, ?String $msg = null)
    {
        $this->reservation = $reservation;
        $this->msg = $msg;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [];
        // return [SparkChannel::class];
    }

    public function toSpark($notifiable)
    {
        return [];
//         return (new SparkNotification)
//             ->action(__('View Reservation'), '/home/reservation/'. $this->reservation->id)
// //            ->icon($this->ad->image())
//             ->body(__('Reservation Deleted'))
//             ;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return [];
        // return (new MailMessage)
        //             ->line('The introduction to the notification.')
        //             ->action('Notification Action', url('/'))
        //             ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
