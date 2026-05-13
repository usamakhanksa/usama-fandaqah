<?php

namespace App\Nova;

use Laravel\Nova\Resource;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\JSON;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Number;

class ReportSchedule extends Resource
{
    public static string $model = \App\Models\ReportSchedule::class;
    public static $title = 'name';
    public static $search = ['name'];

    public static function label()
    {
        return 'Report Schedules';
    }

    public function fields(\Laravel\Nova\Http\Requests\NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),
            Text::make('Name')
                ->sortable()
                ->filterable()
                ->rules('required', 'max:255'),
            Select::make('Report Type')
                ->options([
                    'daily' => 'Daily',
                    'occupancy' => 'Occupancy',
                    'revenue' => 'Revenue',
                    'adr_revpar' => 'ADR & RevPAR',
                    'custom' => 'Custom Report',
                ])
                ->sortable()
                ->filterable(),
            BelongsTo::make('Custom Report', 'customReport', 'App\Nova\CustomReport')
                ->nullable(),
            Select::make('Frequency')
                ->options([
                    'daily' => 'Daily',
                    'weekly' => 'Weekly',
                    'monthly' => 'Monthly',
                ])
                ->sortable(),
            Number::make('Day of Week')
                ->min(0)
                ->max(6)
                ->nullable(),
            Number::make('Day of Month')
                ->min(1)
                ->max(31)
                ->nullable(),
            Text::make('Time')
                ->rules('required'),
            JSON::make('Recipients')
                ->filterable(),
            Select::make('Format')
                ->options([
                    'pdf' => 'PDF',
                    'excel' => 'Excel',
                    'both' => 'Both',
                ]),
            Boolean::make('Is Active')
                ->filterable(),
            DateTime::make('Last Run At')
                ->sortable()
                ->nullable(),
            DateTime::make('Next Run At')
                ->sortable()
                ->nullable(),
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