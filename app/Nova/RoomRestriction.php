<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Http\Requests\NovaRequest;

class RoomRestriction extends Resource
{
    public static $model = \App\Models\RoomRestriction::class;

    public static $title = 'id';

    public static $search = [
        'id', 'restriction_type',
    ];

    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),

            BelongsTo::make('Room', 'room', Room::class),

            Date::make('Start Date')
                ->sortable()
                ->rules('required', 'date'),

            Date::make('End Date')
                ->sortable()
                ->rules('required', 'date', 'after_or_equal:start_date'),

            Select::make('Restriction Type')
                ->options([
                    'cta' => 'Closed to Arrival (CTA)',
                    'ctd' => 'Closed to Departure (CTD)',
                    'min_los' => 'Minimum Length of Stay',
                    'max_los' => 'Maximum Length of Stay',
                    'closed' => 'Closed / Blocked',
                ])
                ->rules('required'),

            Text::make('Value')
                ->nullable()
                ->help('Used for Min/Max LOS values'),

            Text::make('Reason')
                ->nullable(),
        ];
    }
}
