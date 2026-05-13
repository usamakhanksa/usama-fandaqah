<?php

namespace App\Nova\Filters;

use Laravel\Nova\Filters\Filter;
use Laravel\Nova\Http\Requests\NovaRequest;

class ReservationStatusFilter extends Filter
{
    public $name = 'Reservation Status';

    public function apply(NovaRequest $request, $query, $value)
    {
        return $query->where('status', $value);
    }

    public function options(NovaRequest $request)
    {
        return [
            'Confirmed' => 'confirmed',
            'Pending' => 'pending',
            'Canceled' => 'canceled',
        ];
    }
}