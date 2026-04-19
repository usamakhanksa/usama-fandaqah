<?php

namespace App\Nova\Lenses;

use App\Nova\Filters\UnitsMovementFilters\CustomerIdNumber;
use App\Nova\Filters\UnitsMovementFilters\ReservationDate;
use App\Nova\Filters\UnitsMovementFilters\ReservationNumberFilter;
use App\Nova\Filters\UnitsMovementFilters\UnitNumber;
use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Lenses\Lens;
use Laravel\Nova\Http\Requests\LensRequest;

class UnitsMovementReport extends Lens
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

        return $request->withOrdering($request->withFilters(
            $query->where('team_id' , auth()->user()->current_team_id)
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
//            ID::make('ID', 'id'),
            Text::make(__('Unit name') , 'unit')->displayUsing(function($unit){
                return $unit->name ;
            }) ,
            Text::make(__('Unit Number') , 'unit')->resolveUsing(function($unit){
                return $unit->unit_number ;
            }) ,
            Text::make(__('Reservation Number') , 'number')->displayUsing(function($payable_type)use($request){

                return '<a href="" data-attr-id='.$this->id.' class="no-underline dim text-primary font-bold view_reservation">' .
                    $this->number . '</a>';
            })->sortable()->asHtml() ,
            Text::make(__('Date In') , 'date_in') ,
            Text::make(__('Date Out') , 'date_out') ,
            Text::make(__('Creation Date') , 'created_at')->displayUsing(function($reservation){
                return date('Y-m-d' , strtotime($this->created_at)) ;
            }) ,

            Text::make(__('Customer Name') , 'customer')->displayUsing(function($customer){
                return $customer->name;
            }) ,



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
            new UnitNumber() ,
            new CustomerIdNumber(),
            new ReservationNumberFilter(),
            new ReservationDate()
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
        return 'units-movement-report';
    }
}
