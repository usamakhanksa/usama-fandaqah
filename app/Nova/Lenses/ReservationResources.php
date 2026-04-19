<?php

namespace App\Nova\Lenses;

use App\Nova\Customer;
use App\Nova\Filters\ReservationDateRange;
use App\Nova\Filters\SourceFilter;
use App\Nova\Filters\UnitsMovementFilters\CustomerIdNumber;
use App\Nova\Filters\UnitsMovementFilters\ReservationDate;
use App\Nova\Filters\UnitsMovementFilters\ReservationNumberFilter;
use App\Nova\Filters\UnitsMovementFilters\UnitNumber;
use App\Nova\Unit;
use Inspheric\Fields\Indicator;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Lenses\Lens;
use Laravel\Nova\Http\Requests\LensRequest;

class ReservationResources extends Lens
{

    public static function query(LensRequest $request, $query)
    {

        return $request->withOrdering($request->withFilters(
            $query->where('team_id' , auth()->user()->current_team_id)
                    ->orderByDesc('created_at')
        ));
    }


    public function fields(Request $request)
    {
        return [
//            ID::make(__('ID'), 'id')->sortable()->hideFromIndex(),
            Text::make(__('Reservation Number') , 'number')->displayUsing(function(){
                return '<a href="" data-attr-id='.$this->id.' class="no-underline dim text-primary font-bold view_reservation">' . '#' .
                    $this->number . '</a>';
//                return '#' . $this->number ;
            })->onlyOnIndex()->asHtml(),
            BelongsTo::make(__('Reservation Source'), 'source', Customer::class),
            BelongsTo::make(__('Customer'), 'customer', Customer::class)->rules('required'),
            BelongsTo::make(__('Unit Number') , 'unit' , Unit::class)->display('unit_number') ,
            Indicator::make(__('Status'), 'status')
                ->labels([
                    'confirmed' => __('Confirmed'),
                    'canceled' => __('Canceled'),
                ])->colors([
                    'confirmed' => 'green',
                    'canceled' => 'red',
                ]),

            Text::make(__('Reservation Status') , 'id')->displayUsing(function($reservation){

                if(is_null($this->checked_in) && is_null($this->checked_out) ){
                    $label = __('Pending') ;
                    $class = 'text-info' ;
                }else if(!is_null($this->checked_in) &&  is_null($this->checked_out)){
                    $label =  __('Checked In') ;
                    $class =  'text-green-500' ;
                }else{
                    $label =  __('Checked Out');
                    $class =  'text-red-500' ;
                }

                return '<div class="font-bold "><b class="'.$class.'">' . $label . '</b></div>';
            })->asHtml()->onlyOnIndex(),


            Text::make(__('Date In'), 'date_in'),
            Text::make(__('Date Out'), 'date_out'),
            Text::make(__('Nights Count') , 'nights'),
            Text::make(__('Rent Type') , 'id')->displayUsing(function(){
                if($this->rent_type == 1){
                    return __('Daily');
                }elseif($this->rent_type == 2){
                    return __('Monthly');
                }
            }),
            Currency::make(__('Total Credit'), 'total_price')->displayUsing(function(){
                $reservation = $this ;
                $reservation->wallet->refreshBalance();
                // i need balance to find debit and credit
                $balance = $reservation->balance / 100 ;
                if($balance > 0 ){
                    $label = '('.__('credit').')' ;
                    $class = 'text-success' ;
                }elseif ($balance < 0 ){
                    $label =  '('.__('debit').')' ;
                    $class =  'text-danger' ;
                }else{
                    $label =  '' ;
                    $class =  'text-black' ;
                }

                $balance = abs($balance) ;
                return '<div class="font-bold '.$class.'"><b>' . $balance  .' '.__('SAR'). '</b><span class="'.$class.'">  '. $label .' </span></div>';
            })->asHtml(),

        ];

    }

    public function filters(Request $request)
    {
        return [
                new SourceFilter(),
                new ReservationDateRange()
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
        return 'reservation-resources';
    }
}
