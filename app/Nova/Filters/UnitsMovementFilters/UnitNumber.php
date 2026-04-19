<?php

namespace App\Nova\Filters\UnitsMovementFilters;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

use App\Unit ;
use Illuminate\Support\Facades\Auth ;


class UnitNumber extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';


    public $name = 'Filter By Unit Number' ;

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
        return $query->byUnitId($value);
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

        $units = Unit::where('team_id' , '=' , $user_team_id)->whereEnabled(true)->get();
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
