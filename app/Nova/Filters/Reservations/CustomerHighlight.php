<?php

namespace App\Nova\Filters\Reservations;

use App\Customer;
use App\Highlight;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class CustomerHighlight extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'customer-highlight-type-filter';

    public $name = 'Customer Category';

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
        if ($value == "All")
            return $query;
        $customers = Customer::where('highlight_id', $value)->pluck('id');
        return $query->whereIn('customer_id', $customers);
    }

    /**
     * Get the filter's available options.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function options(Request $request)
    {
        $highlights = Highlight::all();
        $filtered = [];
        foreach ($highlights as $highlight) {
            $filtered[] =[
                'name' => $highlight->name ,
                'value' => $highlight->id
            ];
        }

        return $filtered;
    }
}
