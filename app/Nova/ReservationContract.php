<?php

namespace App\Nova;

use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Http\Requests\NovaRequest;

class ReservationContract extends Resource
{
    public static $model = \App\ReservationContract::class;

    public static $title = 'id';

    public static $group = 'Booking Management';

    public static $search = [
        'id', 'uuid', 'status'
    ];

    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),

            BelongsTo::make('Reservation')->searchable(),
            
            Text::make('UUID'),
            Text::make('Version'),
            Text::make('HTML Path', 'html_path'),
            Text::make('Shorten URL Code', 'shorten_url_code'),
            
            Select::make('Status')
                ->options([
                    'pending' => 'Pending',
                    'signed' => 'Signed',
                    'rejected' => 'Rejected',
                ])
                ->displayUsingLabels(),
                
            DateTime::make('Signed At'),
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
        return [
            new Filters\ContractStatusFilter,
        ];
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