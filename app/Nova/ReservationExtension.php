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

class ReservationExtension extends Resource
{
    public static $model = \App\ReservationExtension::class;

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
            BelongsTo::make('Creator', 'created_by', 'App\Nova\User'),
            
            Date::make('Original Check Out', 'original_date_out'),
            Date::make('Extended Check Out', 'extended_date_out'),
            Number::make('Extension Cost', 'extension_cost')->step(0.01),
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