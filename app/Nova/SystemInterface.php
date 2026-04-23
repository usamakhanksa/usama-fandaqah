<?php

namespace App\Nova;

use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Http\Requests\NovaRequest;

class SystemInterface extends Resource {
    public static $model = \App\Models\SystemInterface::class;
    public static $title = 'name';
    public static $search = ['name', 'provider'];

    public function fields(NovaRequest $request): array {
        return [
            ID::make()->sortable(),
            Text::make('Name')->sortable()->rules('required'),
            Text::make('Provider')->sortable(),
            Select::make('Type')->options([
                'government' => 'Government', 'payment_gateway' => 'Payment', 
                'door_lock' => 'Door Lock', 'erp' => 'ERP', 'other' => 'Other'
            ])->sortable(),
            Select::make('Status')->options([
                'connected' => 'Connected', 'disconnected' => 'Disconnected', 
                'degraded' => 'Degraded', 'maintenance' => 'Maintenance'
            ])->sortable(),
            DateTime::make('Last Sync At')->sortable(),
        ];
    }
}
