<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Textarea;
use App\Services\Finance\ReceiptService;
use App\Models\Receipt as ReceiptModel;

class CancelReceipt extends Action
{
    use InteractsWithQueue, Queueable;

    public $name = 'Cancel Receipt';

    public function handle(ActionFields $fields, Collection $models)
    {
        $service = new ReceiptService(new ReceiptModel);
        
        foreach ($models as $model) {
            $service->cancelReceipt($model, auth()->id(), $fields->reason);
        }

        return Action::message('Receipts cancelled successfully.');
    }

    public function fields()
    {
        return [
            Textarea::make('Reason')->rules('required', 'min:5'),
        ];
    }
}
