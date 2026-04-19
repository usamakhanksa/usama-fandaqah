<?php

namespace App\Nova\Filters;

use App\User;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class EmployeeFilter extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';

    public $name = "Employee" ;

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
        return $query->byCreator($value);
    }

    /**
     * Get the filter's available options.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function options(Request $request)
    {
        $current_team_id = auth()->user()->current_team_id ;
        $employees = User::where(['current_team_id' => $current_team_id ])->get() ;
        $arr = [] ;
        foreach($employees as $employee){
           $arr [] = [
               'name' => $employee->name ,
               'value' => $employee->id
           ];
        }
        return $arr ;

    }
}
