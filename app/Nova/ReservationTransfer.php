<?php

namespace App\Nova;

use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Http\Requests\NovaRequest;

class ReservationTransfer extends Resource
{
    public static $model = \App\ReservationTransfer::class;

    public static $title = 'id';

    public static $group = 'Booking Management';

    public static $search = [
        'id', 'reason'
    ];

    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),

            BelongsTo::make('Reservation')->searchable(),
            BelongsTo::make('Creator', 'creator', 'App\Nova\User'),
            
            // Old booking details
            BelongsTo::make('Old Unit', 'old_unit', 'App\Nova\Unit')->readonly(),
            Date::make('Old Check In', 'old_date_in')->readonly(),
            Date::make('Old Check Out', 'old_date_out')->readonly(),
            Number::make('Old Price', 'old_price')->step(0.01)->readonly(),
            
            // New booking details
            BelongsTo::make('New Unit', 'new_unit', 'App\Nova\Unit')->searchable(),
            Date::make('New Check In', 'new_date_in'),
            Date::make('New Check Out', 'new_date_out'),
            Number::make('New Price', 'new_price')->step(0.01),
            
            Textarea::make('Reason'),
            
            DateTime::make('Created At')->onlyOnDetail(),
            DateTime::make('Updated At')->onlyOnDetail(),
        ];
    }

    public function cards(NovaRequest $request)
    {
        return [];
    }

    public function filters(NovaRequest $request)
    {
        return [];
    }

    public function lenses(NovaRequest $request)
    {
        return [];
    }

    public function actions(NovaRequest $request)
    {
        return [];
    }
}
