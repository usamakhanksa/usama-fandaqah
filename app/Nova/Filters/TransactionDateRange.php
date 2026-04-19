<?php

namespace App\Nova\Filters;

use Ampeco\Filters\DateRangeFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TransactionDateRange extends DateRangeFilter
{
    public $name ;
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
        return $query->byDateRange($value);
    }
}
