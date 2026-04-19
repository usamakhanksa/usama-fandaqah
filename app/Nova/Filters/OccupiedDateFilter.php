<?php

namespace App\Nova\Filters;

use Ampeco\Filters\DateRangeFilter;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OccupiedDateFilter extends DateRangeFilter
{
    public $name = "Day";
    public $component = 'date-range-filter';

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
        if (isset($value[0]) and isset($value[1])) {
            $from = Carbon::parse($value[0]);
            $to = Carbon::parse($value[1]);
            $query->whereBetween('created_at', [$from, $to]);
        }
        return $query;
    }
}
