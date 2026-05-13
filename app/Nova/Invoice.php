<?php

namespace App\Nova;

use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Http\Requests\NovaRequest;

class Invoice extends Resource
{
    public static $model = \App\Models\Invoice::class;
    public static $title = 'invoice_number';
    public static $search = ['invoice_number', 'zatca_uuid'];

    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),

            Text::make('Invoice Number')->sortable()->rules('required'),
            
            BelongsTo::make('Team'),
            BelongsTo::make('Guest')->nullable(),
            BelongsTo::make('Company')->nullable(),
            BelongsTo::make('Reservation')->nullable(),

            Select::make('ZATCA Type', 'zatca_invoice_type')->options([
                'standard' => 'Standard',
                'simplified' => 'Simplified',
                'standard_credit_note' => 'Standard Credit Note',
                'simplified_credit_note' => 'Simplified Credit Note',
            ])->displayUsingLabels(),

            DateTime::make('Invoice Date')->sortable(),
            
            Badge::make('Status')->map([
                'draft' => 'info',
                'sent' => 'warning',
                'paid' => 'success',
                'cancelled' => 'danger',
            ]),

            Currency::make('Grand Total')->currency('SAR'),
            Currency::make('VAT Amount')->currency('SAR'),

            Text::make('ZATCA Status')->displayUsing(fn($v) => strtoupper($v))->sortable(),
            Boolean::make('Reported', 'is_zatca_reported'),

            HasMany::make('Items', 'items', InvoiceItem::class),
            
            Text::make('UUID', 'zatca_uuid')->hideFromIndex(),
            Code::make('ZATCA XML')->language('xml')->hideFromIndex(),
            Text::make('QR Code', 'zatca_qr_code')->hideFromIndex(),
        ];
    }
}
