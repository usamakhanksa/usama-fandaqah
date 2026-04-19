<?php

namespace App\Nova\Filters\UnitsMovementFilters;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Laravel\Nova\Filters\DateFilter;

class ReservationDate extends DateFilter
{


    public $name = 'Reservation Date' ;
    public $component = 'date-filter' ;
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
        return $query->byCreatedAt($value);
    }
}
