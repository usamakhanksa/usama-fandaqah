<?php

namespace App\Nova\Lenses;

use App\Nova\Customer;
use App\Nova\Reservation;
use App\Nova\Unit;
use App\Nova\User;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Lenses\Lens;
use Laravel\Nova\Http\Requests\LensRequest;
use App\Nova\Filters\EmployeeFilter;
use App\Nova\Filters\CreationFilter;
use App\Nova\Filters\ContractsUnitNumber;

class EmployeeContracts extends Lens
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
            $query->where('team_id' , '=' , auth()->user()->current_team_id)
                    ->where('status' , '!=' , 'canceled')
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
            BelongsTo::make(__('Employee Name') , 'creator' , User::class),
            BelongsTo::make(__('Unit Number') , 'unit' , Unit::class)->display('unit_number'),
            Text::make(__('Reservation Number') , 'number')->displayUsing(function(){
                return '<a href="" data-attr-id='.$this->id.' class="no-underline dim text-primary font-bold view_reservation">' .
                    $this->number . '</a>';
            })->onlyOnIndex()->asHtml(),
            BelongsTo::make(__('Customer Name') , 'customer' , Customer::class)->display('name'),
            Date::make(__('Creation Date') , 'created_at'),
            Number::make(__('Total Price'), 'total_price')->displayUsing(function($reservation){
                return number_format($this->getServicesSum() + $this->total_price , 2) . ' ' .__('SAR');
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

    /**
     * Get the filters available for the lens.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [
            new EmployeeFilter(),
            new CreationFilter(),
            new ContractsUnitNumber()

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
        return 'employee-contracts';
    }



}
