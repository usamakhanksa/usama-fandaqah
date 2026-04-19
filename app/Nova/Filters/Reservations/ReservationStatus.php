<?php

namespace App\Nova\Filters\Reservations;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class ReservationStatus extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'reservation-status';
    public $name = 'Reservation Status' ;

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
        return $query->byStatus($value);

    }

    /**
     * Get the filter's available options.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function options(Request $request)
    {
        return [
            __('Checked In Reservation') => 'checked_in',
            __('Checked Out Reservation') => 'checked_out',
            __('Canceled Reservation') => 'canceled',
            __('Pending Reservation') => 'pending'
        ];
    }
}
