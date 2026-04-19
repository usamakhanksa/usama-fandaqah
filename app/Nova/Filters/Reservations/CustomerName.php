<?php

namespace App\Nova\Filters\Reservations;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class CustomerName extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $name = 'Customer name' ;
    public $component = 'customer-name';

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
        $value = base64_decode($value) ;
        return $query->customerName($value);
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
