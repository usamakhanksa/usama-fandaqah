<?php

namespace App\Nova\Filters\GuestsReport;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

use App\Unit ;
use Illuminate\Support\Facades\Auth ;


class GenderFilter extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';


    public $name = 'Filter By Gender' ;

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

        return $query->where('gender' , '=' , $value);
    }

    /**
     * Get the filter's available options.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function options(Request $request)
    {
        return [

            __('Male') => 'male' ,
            __('Female') => 'female'

        ];
    }



}
