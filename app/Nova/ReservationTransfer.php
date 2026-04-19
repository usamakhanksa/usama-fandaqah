<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class ReservationTransfer extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\ReservationTransfer';

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

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            // ID::make(__('Id') , 'id')->sortable(),
            BelongsTo::make(__('reservation') , 'reservation' , Reservation::class)->display('number') ,

            BelongsTo::make(__('old Unit Number') , 'old_unit' , Unit::class)->display('unit_number') ,
            Text::make(__('old Date In'), 'old_date_in'),
            Text::make(__('old Date Out'), 'old_date_out'),

            Text::make(__('old Price'), 'old_price'),

            BelongsTo::make(__('new Unit Number') , 'new_unit' , Unit::class)->display('unit_number') ,
            Text::make(__('new Date In'), 'new_date_in'),
            Text::make(__('new Date Out'), 'new_date_out'),
            Text::make(__('new Price'), 'new_price'),

            Text::make(__('Reason'), 'reason'),

            BelongsTo::make(__('Creator'), 'creator', User::class)->display('name')->rules('required'),


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


    public function authorizedToView(Request $request)
    {
        return false;
    }

    public function authorizedToUpdate(Request $request)
    {
        return false;
    }

    public function authorizedToDelete(Request $request)
    {
        return false;
    }

    public static function authorizedToCreate(Request $request)
   {
       return false ;
   }

    // Hide Search Input in resource
    public static function searchable()
    {
        return false;
    }
}
