<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Http\Requests\NovaRequest;

class Booking extends Resource {
    public static $model = \App\Models\Booking::class;
    public static $title = 'reference_code';
    public static $search = ['reference_code', 'guest_name', 'property_reference'];

    public function fields(NovaRequest $request): array {
        return [
            ID::make()->sortable(),
            Text::make('Ref', 'reference_code')->readonly(),
            Text::make('Guest Name')->sortable()->rules('required'),
            Text::make('Property', 'property_reference')->sortable()->rules('required'),
            Date::make('Check In')->sortable()->rules('required'),
            Date::make('Check Out')->sortable()->rules('required'),
            Select::make('Status')->options([
                'pending' => 'Pending', 'confirmed' => 'Confirmed', 
                'cancelled' => 'Cancelled', 'completed' => 'Completed'
            ])->sortable(),
            Currency::make('Total Amount')->currency('SAR')->sortable(),
        ];
    }
}
