<?php

namespace App\Nova;

use App\Models\FinancialTransaction as FinancialTransactionModel;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Http\Requests\NovaRequest;

class FinancialTransaction extends Resource {
    public static $model = FinancialTransactionModel::class;
    public static $title = 'receipt_number';
    public static $search = ['receipt_number', 'booking_reference', 'description'];

    public function fields(NovaRequest $request): array {
        return [
            ID::make()->sortable(),
            Text::make('Receipt Number')->readonly(),
            Text::make('Booking Reference')->sortable(),
            BelongsTo::make('AR Account', 'arAccount', ArAccount::class)->nullable(),
            Select::make('Type')->options([
                'charge' => 'Charge',
                'payment' => 'Payment',
                'refund' => 'Refund',
                'adjustment' => 'Adjustment'
            ])->sortable()->rules('required'),
            Currency::make('Amount')->currency('SAR')->sortable()->rules('required'),
            Text::make('Payment Method'),
            Text::make('Description')->rules('required'),
            Date::make('Transaction Date')->sortable()->rules('required'),
        ];
    }
}
