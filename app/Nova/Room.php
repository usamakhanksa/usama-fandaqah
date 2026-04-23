<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Http\Requests\NovaRequest;

class Room extends Resource
{
    public static $model = \App\Models\Room::class;

    public static $title = 'room_number';

    public static $search = [
        'id', 'room_number',
    ];

    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),

            BelongsTo::make('Room Type', 'roomType', RoomType::class),

            Text::make('Room Number')
                ->sortable()
                ->rules('required', 'max:255'),

            Select::make('Status')
                ->options([
                    'clean' => 'Clean',
                    'dirty' => 'Dirty',
                    'inspected' => 'Inspected',
                    'out_of_order' => 'Out of Order',
                    'maintenance' => 'Maintenance',
                ])
                ->default('clean')
                ->rules('required'),

            HasMany::make('Housekeeping Tasks', 'housekeepingTasks', HousekeepingTask::class),
            HasMany::make('Restrictions', 'restrictions', RoomRestriction::class),
        ];
    }
}
