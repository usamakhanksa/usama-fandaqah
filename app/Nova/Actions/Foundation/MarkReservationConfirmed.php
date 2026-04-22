<?php
namespace App\Nova\Actions\Foundation;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class MarkReservationConfirmed extends Action
{
    use InteractsWithQueue, Queueable;
    public function handle(ActionFields $fields, Collection $models)
    {
        foreach($models as $model){ $model->update(['status'=>'confirmed','confirmed_at'=>now()]); }
        return Action::message('Reservations confirmed.');
    }
    public function fields(): array { return []; }
}
