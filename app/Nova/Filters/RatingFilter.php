<?php

namespace App\Nova\Filters;

use Laravel\Nova\Filters\Filter;
use Laravel\Nova\Http\Requests\NovaRequest;

class RatingFilter extends Filter
{
    public $name = 'Rating';

    public function apply(NovaRequest $request, $query, $value)
    {
        return $query->where('rating', $value);
    }

    public function options(NovaRequest $request)
    {
        return [
            'Excellent (5)' => 5,
            'Good (4)' => 4,
            'Average (3)' => 3,
            'Poor (2)' => 2,
            'Terrible (1)' => 1,
        ];
    }
}