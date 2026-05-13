<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;

class CommissionPaymentDetail extends Resource
{
    public static $model = \App\Models\CommissionPaymentDetail::class;
    public static $displayInNavigation = false;

    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),
            BelongsTo::make('Commission Payment', 'payment', CommissionPayment::class),
            BelongsTo::make('Reservation', 'reservation', Reservation::class),
            Currency::make('Room Revenue')->currency('SAR'),
            Number::make('Commission Rate')->step(0.01),
            Currency::make('Commission Amount')->currency('SAR'),
        ];
    }
}
