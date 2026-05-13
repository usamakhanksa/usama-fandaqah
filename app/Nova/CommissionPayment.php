<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Currency;

class CommissionPayment extends Resource
{
    public static $model = \App\Models\CommissionPayment::class;
    public static $title = 'payment_number';
    public static $search = ['id', 'payment_number', 'reference_number'];

    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),
            Text::make('Payment Number')->readonly(),
            BelongsTo::make('Travel Agent', 'travelAgent', Company::class),
            Date::make('Period From', 'commission_period_from'),
            Date::make('Period To', 'commission_period_to'),
            Currency::make('Total Commission')->currency('SAR'),
            Currency::make('Total Paid')->currency('SAR'),
            Select::make('Payment Method')->options([
                'cash' => 'Cash',
                'card' => 'Card',
                'bank_transfer' => 'Bank Transfer',
                'cheque' => 'Cheque',
            ]),
            BelongsTo::make('Bank')->nullable(),
            Text::make('Reference Number'),
            Date::make('Payment Date'),
            Select::make('Status')->options([
                'pending' => 'Pending',
                'partial' => 'Partial',
                'paid' => 'Paid',
                'cancelled' => 'Cancelled',
            ]),
            BelongsTo::make('Created By', 'creator', User::class)->readonly(),
            
            HasMany::make('Details', 'details', CommissionPaymentDetail::class),
        ];
    }
}
