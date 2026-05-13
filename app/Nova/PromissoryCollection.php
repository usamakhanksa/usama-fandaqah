<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Http\Requests\NovaRequest;

class PromissoryCollection extends Resource
{
    public static $model = \App\Models\PromissoryCollection::class;

    public static $displayInNavigation = false;

    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),

            BelongsTo::make(__('Note'), 'promissoryNote', PromissoryNote::class),

            Date::make(__('Date'), 'collection_date')->sortable(),

            Currency::make(__('Amount'), 'amount')->currency('SAR'),

            Text::make(__('Method'), 'payment_method'),

            Badge::make(__('Status'), 'status')->map([
                'confirmed' => 'success',
                'reversed' => 'danger',
            ]),

            BelongsTo::make(__('Collector'), 'collector', User::class)->onlyOnDetail(),
        ];
    }
}
