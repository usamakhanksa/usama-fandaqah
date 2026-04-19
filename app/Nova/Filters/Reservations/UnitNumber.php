<?php

namespace App\Nova\Filters\Reservations;

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
    public $component = 'unit-number';
    public $name = 'Unit Number' ;

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

        $units = Unit::where('team_id' , '=' , $user_team_id)->get(); 
        $arr = [] ; 
        foreach($units as $unit){
           $arr [] = [
               'name' => $unit->unit_number   . ' - ' . $unit->name , 
               'value' => $unit->id
           ]; 
        }
        
       
        return $arr ; 
    }


 
}
