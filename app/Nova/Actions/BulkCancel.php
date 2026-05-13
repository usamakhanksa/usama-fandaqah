<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Textarea;

class BulkCancel extends Action
{
    use InteractsWithQueue, Queueable;

    public $name = 'Bulk Cancel';

    public function handle(ActionFields $fields, Collection $models)
    {
        foreach ($models as $reservation) {
            $reservation->update([
                'status' => 'canceled',
                'cancellation_reason' => $fields->reason ?? 'Bulk cancellation'
            ]);
        }

        return Action::message('Selected reservations have been canceled.');
    }

    public function fields()
    {
        return [
            Textarea::make('Reason', 'reason')
                ->help('Provide a reason for canceling these reservations.')
                ->rules('required', 'max:500'),
        ];
    }
}