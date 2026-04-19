<?php

namespace App\Notifications;

use App\Reservation;
use App\Team;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Config;
use Liliom\Unifonic\UnifonicFacade;
use Vinkla\Hashids\Facades\Hashids;

class ReservationRating extends Notification implements ShouldQueue
{
    use Queueable;

    public $tries = 1;
    /** @var Reservation */
    public $reservation;

    /** @var Team */
    public $team;

    /** @var array */
    private $to;

    /**
     * ReservationRating constructor.
     * @param Reservation $reservation
     * @param array $to
     */
    public function __construct(Reservation $reservation, array $to)
    {
        $this->reservation = $reservation;
        $this->team = $this->reservation->team;

        $this->to = $to;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return $this->to;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $url = url(route('rating', ['reservation'   =>  Hashids::encode($this->reservation->id)]));
        $s = "كيف كانت إقامتك - للحجز {$this->reservation->number}";
        return (new MailMessage)->from('postmaster@app.fandaqah.com' , $this->reservation->team->name)->subject($s)->view(
            'email.rating', ['reservation' => $this->reservation, 'url' =>  $url]
        );
    }

    public function toSms($notifiable)
    {
        $phone = preg_replace('/\s+/', '', $notifiable->phone);
        $phone = preg_replace('/[^0-9\-]/', '', $phone);

        $message = "مرحبا {$this->reservation->customer->name}";
        $message.= PHP_EOL;
        $message.= "كيف كانت تجربتك في {$this->reservation->team->name}";
        $message.= PHP_EOL;
        $message.= "قيم تجربتك من خلال الرابط :";
        $message.= PHP_EOL;
        $message.= url(route('rating', ['reservation'   =>  Hashids::encode($this->reservation->id)]));

        try {
            sendSms($this->reservation->team_id, $message, intval($phone));
            // UnifonicFacade::send(intval($phone), $message);
        } catch (\Exception $exception) {

        }
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
