<?php

namespace App\Nova\Filters;

use Laravel\Nova\Filters\DateFilter;
use Laravel\Nova\Http\Requests\NovaRequest;

class ReservationDateFilter extends DateFilter
{
    public $name = 'Reservation Date Range';

    public function apply(NovaRequest $request, $query, $value)
    {
        return $query->whereBetween('date_in', [
            $value.' 00:00:00', 
            $value.' 23:59:59'
        ]);
    }
}