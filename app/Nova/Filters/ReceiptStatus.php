<?php

namespace App\Nova\Filters;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class ReceiptStatus extends Filter
{
    public $name = 'Status';

    public function apply(Request $request, $query, $value)
    {
        return $query->where('status', $value);
    }

    public function options(Request $request)
    {
        return [
            'Draft' => 'draft',
            'Confirmed' => 'confirmed',
            'Cancelled' => 'cancelled',
            'Refunded' => 'refunded',
        ];
    }
}
