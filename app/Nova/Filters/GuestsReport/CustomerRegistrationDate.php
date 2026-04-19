<?php

namespace App\Nova\Filters\GuestsReport;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Laravel\Nova\Filters\DateFilter;
use Ampeco\Filters\DateRangeFilter;

class CustomerRegistrationDate extends DateRangeFilter
{

    public $name = 'Customer Registration Date' ;
//    public $component = 'date-filter' ;
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
        return $query->byRegistrationDate($value);

    }
}
