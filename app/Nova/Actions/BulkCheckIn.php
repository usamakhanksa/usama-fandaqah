<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Carbon\Carbon;

class BulkCheckIn extends Action
{
    use InteractsWithQueue, Queueable;

    public $name = 'Bulk Check-In';

    public function handle(ActionFields $fields, Collection $models)
    {
        foreach ($models as $reservation) {
            if ($reservation->status !== 'canceled') {
                $reservation->update([
                    'checked_in' => Carbon::now(),
                    'status' => 'confirmed'
                ]);
            }
        }

        return Action::message('Selected reservations have been checked in successfully.');
    }
}