<?php

namespace App\Nova\Filters;

use Laravel\Nova\Filters\Filter;
use Laravel\Nova\Http\Requests\NovaRequest;

class ContractStatusFilter extends Filter
{
    public $name = 'Contract Status';

    public function apply(NovaRequest $request, $query, $value)
    {
        return $query->where('status', $value);
    }

    public function options(NovaRequest $request)
    {
        return [
            'Pending' => 'pending',
            'Signed' => 'signed',
            'Rejected' => 'rejected',
        ];
    }
}