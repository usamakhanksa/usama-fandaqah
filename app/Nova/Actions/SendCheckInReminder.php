<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Illuminate\Support\Facades\Mail;
use App\Mail\CheckInReminderMail;
use App\Reservation;

class SendCheckInReminder extends Action
{
    use InteractsWithQueue, Queueable;

    public $name = 'Send Check-In Reminder';

    public function handle(ActionFields $fields, Collection $models)
    {
        foreach ($models as $reservation) {
            // Send email reminder to customer
            Mail::to($reservation->customer->email)->send(
                new CheckInReminderMail($reservation, $fields->custom_message)
            );

            // Optionally also send SMS notification
            // $reservation->customer->notify(new CheckInReminderSMS($reservation));
        }

        return Action::message('Check-in reminders sent successfully!');
    }

    public function fields()
    {
        return [
            Textarea::make('Custom Message', 'custom_message')
                ->help('Enter a custom message to include in the reminder.')
                ->rules('required', 'max:500'),
        ];
    }
}