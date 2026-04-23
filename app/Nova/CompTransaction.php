<?php

namespace App\Nova;

use App\Models\CompTransaction as CompTransactionModel;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Http\Requests\NovaRequest;

class CompTransaction extends Resource {
    public static $model = CompTransactionModel::class;
    public static $title = 'booking_reference';
    public static $search = ['booking_reference', 'reason', 'approved_by'];

    public function fields(NovaRequest $request): array {
        return [
            ID::make()->sortable(),
            Text::make('Booking Reference')->sortable()->rules('required'),
            Select::make('Department')->options([
                'rooms' => 'Rooms',
                'f_and_b' => 'Food & Beverage',
                'spa' => 'SPA',
                'transport' => 'Transport',
                'other' => 'Other'
            ])->sortable()->rules('required')->displayUsingLabels(),
            Currency::make('Value Amount')->currency('SAR')->sortable()->rules('required'),
            Text::make('Reason')->rules('required'),
            Text::make('Approved By')->sortable()->rules('required'),
            Date::make('Date Posted')->sortable()->rules('required'),
        ];
    }
}
