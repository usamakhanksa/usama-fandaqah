<?php

namespace App\Nova\Filters;

use Laravel\Nova\Filters\Filter;
use Laravel\Nova\Http\Requests\NovaRequest;

class ReservationCategoryTypeFilter extends Filter
{
    public $name = 'Reservation Category Type';

    public function apply(NovaRequest $request, $query, $value)
    {
        return $query->where('reservation_category_type', $value);
    }

    public function options(NovaRequest $request)
    {
        return [
            'Normal' => 'Normal',
            'Complimentary' => 'Complimentary',
            'HouseUse' => 'HouseUse',
            'DayUse' => 'DayUse',
        ];
    }
}