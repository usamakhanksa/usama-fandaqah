<?php

namespace App\Listeners;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\OnlineReservationConfirmed;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Mail\Customer\OnlineReservationConfirmedForNewWebsite;
use Vinkla\Hashids\Facades\Hashids;
class SendAutoMessageWhenOnlineReservationConfirmed implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  OnlineReservationConfirmed  $event
     * @return void
     */
    public function handle(OnlineReservationConfirmed $event)
    {
        $this->sendEmail($event);
        // $this->sendSms($event);
    }

    public function sendEmail($event)
    {
        if ($event->reservation->customer && $event->reservation->customer->email) {
            $url =  $event->reservation->team->private_domain ? '//' . $event->reservation->team->private_domain . '/confirmation?id=' . Hashids::encode($event->reservation->id) :  $event->reservation->team->slug . '.' . env('CUSTOMER_WEBSITE_DOMAIN')  . '/confirmation?id=' . Hashids::encode($event->reservation->id);
            $subject = 'تم تآكيد حجزك رقم ';
            $subject .= $event->reservation->number;
            $res_url =  $event->reservation->team->private_domain ? '//' . $event->reservation->team->private_domain . '/confirmation?id=' . Hashids::encode($event->reservation->id) :  $event->reservation->team->slug . '.' . env('CUSTOMER_WEBSITE_DOMAIN')  . '/confirmation?id=' . Hashids::encode($event->reservation->id);

            $data = [
                'to' => $event->reservation->customer->email,
                'reply_to' => $event->reservation->team->owner->email,
                'subject' => $subject,
                'html' => view('email.reservation_confirmed')
                    ->with(['reservation' => $event->reservation, 'settings' => $event->reservation->team->websiteSetting, 'res_url' => $res_url, 'url' => $url])
                    ->render(),
            ];

            $send = sendMailUsingMailMicroservice($data);
            // Mail::to($event->reservation->customer->email)->send(new OnlineReservationConfirmedForNewWebsite($event->reservation));
        }
    }

    public function sendSms($event)
    {
        $message = $this->formatSmsMessage($event->reservation);
        try {
            if ($event->reservation->customer && $event->reservation->customer->phone != null) {
                $phone = intval($event->reservation->customer->phone);
                sendSms($event->reservation->team_id, $message, $phone);
            }
        } catch (Exception $exception) {
        }
    }

    public function formatSmsMessage($reservation)
    {
        $message = '';
        $message .= PHP_EOL;
        $message .= 'رقم العقد :' . $reservation->number;
        $message .= PHP_EOL;
        $message .= ' من : ' . date('Y/m/d', strtotime($reservation->date_in)) . ' الى  :  ' . date('Y/m/d', strtotime($reservation->date_out));
        $message .= PHP_EOL;
        $message .= ' الوحدة :' . $reservation->unit->unit_category->name;
        $message .= PHP_EOL;
        $message .= ' المبلغ :' . $reservation->price . ' ' . __('SAR');
        $message .= PHP_EOL;
        return $message;
    }
}
