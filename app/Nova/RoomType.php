<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Http\Requests\NovaRequest;

class RoomType extends Resource
{
    public static $model = \App\Models\RoomType::class;

    public static $title = 'name';

    public static $search = [
        'id', 'name', 'code',
    ];

    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),

            Text::make('Name')
                ->sortable()
                ->rules('required', 'max:255'),

            Text::make('Code')
                ->sortable()
                ->rules('required', 'max:50'),

            Currency::make('Base Price')
                ->sortable()
                ->rules('required'),

            Number::make('Max Occupancy')
                ->sortable()
                ->rules('required', 'min:1'),

            HasMany::make('Rooms', 'rooms', Room::class),
        ];
    }
}
