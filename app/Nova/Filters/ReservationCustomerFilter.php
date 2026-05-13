<?php

namespace App\Nova\Filters;

use Laravel\Nova\Filters\Filter;
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Customer;

class ReservationCustomerFilter extends Filter
{
    public $name = 'Customer';

    public function apply(NovaRequest $request, $query, $value)
    {
        return $query->where('customer_id', $value);
    }

    public function options(NovaRequest $request)
    {
        return Customer::all()->pluck('id', 'id')->toArray();
    }
}