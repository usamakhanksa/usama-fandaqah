<?php

namespace App\Nova\Filters\Reservations;

use Ampeco\Filters\DateRangeFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DateInFilter extends DateRangeFilter
{
    public $name = 'Date In';
    public $component = 'date-range-filter';

    /**
     * Apply the filter to the given query.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, $query, $value)
    {
        return $query->dateInBetween($value[0], $value[1]);
    }
}
