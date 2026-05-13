<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Http\Requests\NovaRequest;

class InvoiceTransferItem extends Resource
{
    public static $model = \App\Models\InvoiceTransferItem::class;

    public static $displayInNavigation = false;

    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),

            BelongsTo::make(__('Transfer'), 'invoiceTransfer', InvoiceTransfer::class),

            BelongsTo::make(__('From Item'), 'fromItem', InvoiceItem::class),

            Currency::make(__('Amount'), 'amount')->currency('SAR'),
        ];
    }
}
