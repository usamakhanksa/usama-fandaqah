<?php

namespace App\Nova;

use Laravel\Nova\Resource;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\JSON;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\DateTime;

class CustomReport extends Resource
{
    public static string $model = \App\Models\CustomReport::class;
    public static $title = 'name';
    public static $search = ['name', 'description'];

    public static function label()
    {
        return 'Custom Reports';
    }

    public function fields(\Laravel\Nova\Http\Requests\NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),
            Text::make('Name')
                ->sortable()
                ->filterable()
                ->rules('required', 'max:255'),
            Textarea::make('Description')
                ->alwaysShow()
                ->rules('nullable'),
            Select::make('Module')
                ->options([
                    'reservations' => 'Reservations',
                    'finance' => 'Finance',
                    'rooms' => 'Rooms',
                    'guests' => 'Guests',
                    'pos' => 'POS',
                ])
                ->sortable()
                ->filterable()
                ->rules('required'),
            JSON::make('Columns', fn () => null)
                ->onlyOnDetail(),
            JSON::make('Filters', fn () => null)
                ->onlyOnDetail(),
            Text::make('Sort By')
                ->nullable(),
            Select::make('Sort Direction')
                ->options([
                    'asc' => 'Ascending',
                    'desc' => 'Descending',
                ])
                ->nullable(),
            Text::make('Group By')
                ->nullable(),
            Boolean::make('Is Shared')
                ->filterable(),
            BelongsTo::make('Team')
                ->sortable()
                ->filterable(),
            BelongsTo::make('Creator', 'creator', 'App\Nova\User')
                ->sortable()
                ->nullable(),
            DateTime::make('Created At')
                ->sortable()
                ->onlyOnIndex(),
            DateTime::make('Updated At')
                ->sortable()
                ->onlyOnIndex(),
        ];
    }
}