<?php

namespace App\Nova\Filters\UnitsMovementFilters;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class ReservationNumberFilter extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'reservation-number-filter';

    public $name = "Reservation Number Filter" ;
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
        return $query->byNumber($value);
    }

    /**
     * Get the filter's available options.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function options(Request $request)
    {
        return [];
    }
}
