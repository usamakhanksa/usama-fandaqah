<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Textarea;

class MarkAsNoShow extends Action
{
    use InteractsWithQueue, Queueable;

    public $name = 'Mark As No-Show';

    public function handle(ActionFields $fields, Collection $models)
    {
        foreach ($models as $reservation) {
            $reservation->update([
                'noshow_flag' => true,
                'status' => 'canceled',
                'cancellation_reason' => $fields->reason ?? 'No-show'
            ]);
        }

        return Action::message('Selected reservations have been marked as no-show.');
    }

    public function fields()
    {
        return [
            Textarea::make('Reason', 'reason')
                ->help('Provide a reason for marking these reservations as no-show.')
                ->rules('required', 'max:500'),
        ];
    }
}