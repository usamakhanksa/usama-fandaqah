<?php

namespace App\Nova\Filters\GuestsReport;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class PhoneNumberFilter extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'phone-number-filter';

    public $name = "Customer Phone Number" ;
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

        return $query->byPhoneNumber($value);

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
