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

class InvoiceTransfer extends Resource
{
    public static $model = \App\Models\InvoiceTransfer::class;

    public static $title = 'transfer_number';

    public static $search = [
        'id', 'transfer_number'
    ];

    public static function group()
    {
        return __('Finance');
    }

    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),

            Text::make(__('Transfer Number'), 'transfer_number')->sortable(),

            BelongsTo::make(__('From Invoice'), 'fromInvoice', Invoice::class),
            BelongsTo::make(__('To Invoice'), 'toInvoice', Invoice::class)->nullable(),

            Date::make(__('Date'), 'transfer_date')->sortable(),

            Currency::make(__('Total Amount'), 'total_amount')->currency('SAR'),

            Badge::make(__('Status'), 'status')->map([
                'pending' => 'warning',
                'approved' => 'info',
                'rejected' => 'danger',
                'completed' => 'success',
                'reversed' => 'danger',
            ]),

            Text::make(__('Reason'), 'reason')->onlyOnDetail(),

            BelongsTo::make(__('Created By'), 'creator', User::class)->onlyOnDetail(),
            
            HasMany::make(__('Items'), 'items', InvoiceTransferItem::class),
        ];
    }
}
