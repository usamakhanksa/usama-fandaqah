<?php

namespace App\Nova\Filters;

use App\Reservation;
use App\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Nova\Filters\Filter;


class ContractsUnitNumber extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';


    public $name = 'Reservation Unit Number' ;

    /**
     * Apply the filter to the given query.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, $query, $value)
    {
        return $query->contractByUnitNumber($value);
    }

    /**
     * Get the filter's available options.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function options(Request $request)
    {
        $user_team_id = Auth::user()->current_team_id ;

        $units = Unit::where('team_id' , '=' , $user_team_id)->get();
        $arr = [] ;
        foreach($units as $unit){
           $arr [] = [
               'name' => $unit->unit_number ,
               'value' => $unit->id
           ];
        }
        return $arr ;
    }



}
