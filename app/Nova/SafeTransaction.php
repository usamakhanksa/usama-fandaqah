<?php

namespace App\Nova;

use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Http\Requests\NovaRequest;

class SafeTransaction extends Resource {
    public static $model = \App\Models\SafeTransaction::class;
    public static $title = 'reference_number';
    public static $search = ['reference_number', 'description', 'category'];

    public function fields(NovaRequest $request): array {
        return [
            ID::make()->sortable(),
            Text::make('Ref', 'reference_number')->readonly(),
            Select::make('Type')->options(['deposit' => 'Deposit', 'withdrawal' => 'Withdrawal'])->sortable(),
            Currency::make('Amount')->currency('SAR')->sortable(),
            Text::make('Category')->sortable(),
            Date::make('Date', 'transaction_date')->sortable(),
            Text::make('Performed By')->sortable(),
        ];
    }
}
