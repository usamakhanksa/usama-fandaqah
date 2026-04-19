<?php

namespace App\Nova;
use App\Scopes\TeamScope;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Support\Collection;

class UnitMaintenance extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\UnitMaintenance';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
    ];


    // Override BradCrumb title
    public static function label()
    {
        return __('Service under maintenance') ;
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
            BelongsTo::make(__('Unit Number') , 'unit' , Unit::class)->display('unit_number') ,
            BelongsTo::make(__('Creator'), 'creator', User::class)->display('name')->rules('required'),
            DateTime::make(__('Start At'), 'start_at'),
            BelongsTo::make(__('Completed by'), 'completedBy', User::class)->display('name')->rules('required'),
            DateTime::make(__('Completed At'), 'completed_at'),
            Text::make(__('Time Spent'), 'time_spent'),
            Text::make(__('Type'), 'action_type.name_en'),
            Text::make(__('Expected Date Time'), 'expected_date_time'),
            Text::make(__('Note'), 'note'),
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
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
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


    // Hide Search Input in resource
    public static function searchable()
    {
        return false;
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

    public static function detailQuery(NovaRequest $request, $query)
    {
        $query->withGlobalScope('team_id', new TeamScope);
        return parent::detailQuery($request, $query);
    }
}
