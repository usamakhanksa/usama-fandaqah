<?php

namespace App\Nova\Lenses;

use App\Nova\Filters\GuestsReport\CustomerRegistrationDate;
use App\Nova\Filters\GuestsReport\GenderFilter;
use App\Nova\Filters\GuestsReport\PhoneNumberFilter;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Illuminate\Http\Request;
use Laravel\Nova\Lenses\Lens;
use Laravel\Nova\Http\Requests\LensRequest;
use App\Nova\Filters\GuestsReport\CustomerIdNumber ;


class GuestsReport extends Lens
{
    /**
     * Get the query builder / paginator for the lens.
     *
     * @param  \Laravel\Nova\Http\Requests\LensRequest  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return mixed
     */
    public static function query(LensRequest $request, $query)
    {

        $current_team_id = auth()->user()->current_team_id ;

        return $request->withOrdering($request->withFilters(
            $query->where('team_id' , $current_team_id)
                    ->orderByDesc('created_at')
        ));

    }

    /**
     * Get the fields available to the lens.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
//            ID::make('#', 'id')->sortable()->hideFromIndex(),
            Text::make(__('Customer name') , 'name')->sortable() ,
            Text::make(__('Customer Highlight'), 'label')
                ->asHtml()
                ->exceptOnForms(),
            Text::make(__('ID Number') , 'id_number') ,
            Text::make(__('Phone') , 'phone')->displayUsing(function($val){
                    return '<span style="display: inline-block;direction: ltr">' . $val . '</span>' ;
            })->asHtml() ,
            Text::make(__('Gender') , 'gender')->displayUsing(function($val){
                return __(\ucfirst($val)) ;
            }) ,
            Text::make(__('Reservation Count') , 'reservations_count') ,
            Text::make(__('Nationality') , 'nationality_string') ,
        ];
    }

    /**
     * Get the filters available for the lens.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [
            new CustomerIdNumber,
            new CustomerRegistrationDate(),
            new PhoneNumberFilter(),
            new GenderFilter()
        ];
    }

    /**
     * Get the actions available on the lens.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return parent::actions($request);
    }

    /**
     * Get the URI key for the lens.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'guests-report';
    }
}
