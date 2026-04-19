<?php

namespace App\Nova\Filters;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class ReservationIdFilter extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'id-number-filter';

    public $name = "Customer Id Number" ; 

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
       
        return  $query->whereHas('reservation', function ($reservation) use($value) {
            // $query->where('id_number', "like", "%$value%");
            $reservation->whereHas('customer' , function($customer) use($value){

                $customer->where('id_number', "LIKE", '%' . $value . '%');

            });
        });
        
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
