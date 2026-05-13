<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Http\Requests\NovaRequest;

class CreditNoteItem extends Resource
{
    public static $model = \App\Models\CreditNoteItem::class;

    public static $displayInNavigation = false;

    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),

            BelongsTo::make(__('Credit Note'), 'creditNote', CreditNote::class),

            Text::make(__('Product Name'), 'product_name'),

            Number::make(__('Quantity'), 'quantity'),

            Currency::make(__('Unit Price'), 'unit_price')->currency('SAR'),

            Currency::make(__('Sub Total'), 'sub_total')->currency('SAR'),

            Currency::make(__('VAT Amount'), 'vat_amount')->currency('SAR'),

            Currency::make(__('Total Amount'), 'total_amount')->currency('SAR'),
        ];
    }
}
