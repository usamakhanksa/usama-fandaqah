<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

class HousekeepingTask extends Resource
{
    public static $model = \App\Models\HousekeepingTask::class;

    public static $title = 'id';

    public static $search = [
        'id', 'assigned_to',
    ];

    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),

            BelongsTo::make('Room', 'room', Room::class),

            Text::make('Assigned To')
                ->sortable()
                ->rules('nullable', 'max:255'),

            Select::make('Task Type')
                ->options([
                    'daily_refresh' => 'Daily Refresh',
                    'deep_clean' => 'Deep Clean',
                    'inspection' => 'Inspection',
                    'maintenance' => 'Maintenance',
                ])
                ->rules('required'),

            Select::make('Status')
                ->options([
                    'pending' => 'Pending',
                    'in_progress' => 'In Progress',
                    'completed' => 'Completed',
                ])
                ->default('pending')
                ->rules('required'),

            Textarea::make('Notes')
                ->nullable(),

            DateTime::make('Scheduled At')
                ->sortable(),

            DateTime::make('Completed At')
                ->sortable()
                ->exceptOnForms(),
        ];
    }
}
