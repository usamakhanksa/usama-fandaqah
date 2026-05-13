<?php

namespace App\Nova\Filters;

use Laravel\Nova\Filters\Filter;
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Unit;

class ReservationUnitFilter extends Filter
{
    public $name = 'Unit';

    public function apply(NovaRequest $request, $query, $value)
    {
        return $query->where('unit_id', $value);
    }

    public function options(NovaRequest $request)
    {
        return Unit::all()->pluck('id', 'id')->toArray();
    }
}