<?php

namespace App\Nova\Filters;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class ReceiptPaymentMethod extends Filter
{
    public $name = 'Payment Method';

    public function apply(Request $request, $query, $value)
    {
        return $query->where('payment_method', $value);
    }

    public function options(Request $request)
    {
        return [
            'Cash' => 'cash',
            'Card' => 'card',
            'Bank Transfer' => 'bank_transfer',
            'Cheque' => 'cheque',
            'Online' => 'online',
            'Other' => 'other',
        ];
    }
}
