<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Http\Requests\NovaRequest;

class PromissoryNote extends Resource
{
    public static $model = \App\Models\PromissoryNote::class;

    public static $title = 'promissory_number';

    public static $search = [
        'id', 'promissory_number', 'signatory_name'
    ];

    public static function group()
    {
        return __('Finance');
    }

    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),

            Text::make(__('Note Number'), 'promissory_number')->sortable(),

            BelongsTo::make(__('Guest'), 'guest', Guest::class)->nullable(),
            BelongsTo::make(__('Company'), 'company', Company::class)->nullable(),

            Currency::make(__('Total Amount'), 'amount')->currency('SAR'),
            Currency::make(__('Collected'), 'collected_amount')->currency('SAR'),
            Currency::make(__('Remaining'), 'remaining_amount')->currency('SAR'),

            Date::make(__('Due Date'), 'due_date')->sortable(),

            Badge::make(__('Status'), 'status')->map([
                'pending' => 'warning',
                'partially_collected' => 'info',
                'collected' => 'success',
                'defaulted' => 'danger',
                'cancelled' => 'danger',
                'renewed' => 'info',
            ]),

            Badge::make(__('Overdue'), 'is_overdue')->map([
                true => 'danger',
                false => 'success',
            ]),

            Text::make(__('Signatory'), 'signatory_name')->onlyOnDetail(),

            HasMany::make(__('Collections'), 'collections', PromissoryCollection::class),
        ];
    }
}
