<?php

namespace App\Nova;

use App\Nova\Filters\OccupiedDateFilter;
use App\Nova\Metrics\OccupiedPerDay;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Http\Requests\NovaRequest;
use R64\NovaFields\Text;
use Surelab\BigFilters\BigFilters;
use Surelab\TotalOccupied\TotalOccupied;

class Occupied extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Occupied';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'created_at';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'created_at',
    ];


    // Override BradCrumb title
    public static function label()
    {
        return __('Occupieds') ;
    }


    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            Text::make(__('Day'), function () {
                return __($this->created_at->format('l'));
            }),
            Date::make(__('Day'), 'created_at'),
            Text::make(__('Units Count'), 'units_count'),
            Text::make(__('Available Units'), 'available'),
            Text::make(__('under cleaning'), 'cleaning'),
            Text::make(__('under maintenance'), 'maintenance'),
            Text::make(__('Booked Units'), 'booked'),
            Text::make(__('Occupied Units'), 'occupied'),
            Text::make(__('Occupied Percentage'), function () {
                return number_format($this->percentage, 2, '.', '') . " %";
            }),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [
            (new BigFilters())->hideFilterTitle(),
//            (new TotalOccupied()),
            // new OccupiedPerDay,
        ];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [
            (new OccupiedDateFilter())
        ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }


    public static function authorizedToCreate(Request $request)
    {
        return false;
    }

    public function authorizedToDelete(Request $request)
    {
        return false;
    }

    public function authorizedToForceDelete(Request $request)
    {
        return false;
    }

    public function authorizedToUpdate(Request $request)
    {
        return false;
    }

    public function authorizedToDetails(Request $request)
    {
        return false;
    }

    public function authorizedToView(Request $request)
    {
        return false;
    }

    // Hide Search Input in resource
    public static function searchable()
    {
        return false;
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        $query->where('team_id', '=', \Auth::user()->current_team_id);

        return $query;
    }
}
