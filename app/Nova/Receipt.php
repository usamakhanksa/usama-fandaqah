<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Http\Requests\NovaRequest;

class Receipt extends Resource
{
    public static $model = \App\Models\Receipt::class;
    public static $title = 'receipt_number';
    public static $search = ['receipt_number', 'description'];

    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),

            Text::make('Receipt Number')
                ->readonly()
                ->sortable(),

            Date::make('Receipt Date')
                ->rules('required')
                ->sortable(),

            BelongsTo::make('Team', 'team', Team::class),
            BelongsTo::make('Reservation', 'reservation', Reservation::class)->nullable(),
            BelongsTo::make('Guest', 'guest', Guest::class)->nullable(),
            BelongsTo::make('Company', 'company', Company::class)->nullable(),

            Currency::make('Amount')
                ->currency('SAR')
                ->rules('required', 'numeric', 'min:0')
                ->sortable(),

            Select::make('Payment Method')
                ->options([
                    'cash' => 'Cash',
                    'card' => 'Card',
                    'bank_transfer' => 'Bank Transfer',
                    'cheque' => 'Cheque',
                    'online' => 'Online',
                    'other' => 'Other',
                ])
                ->rules('required'),

            Text::make('Currency')->default('SAR')->hideFromIndex(),
            Number::make('Exchange Rate')->default(1.0000)->step(0.0001)->hideFromIndex(),

            Badge::make('Status')->map([
                'draft' => 'info',
                'confirmed' => 'success',
                'cancelled' => 'danger',
                'refunded' => 'warning',
            ])->sortable(),

            Text::make('Reference Number')->hideFromIndex(),
            Text::make('Bank Name')->hideFromIndex(),
            Text::make('Cheque Number')->hideFromIndex(),
            Text::make('Card Last Four')->hideFromIndex(),

            Textarea::make('Description'),
            Textarea::make('Notes')->hideFromIndex(),

            DateTime::make('Cancelled At')->onlyOnDetail(),
            BelongsTo::make('Cancelled By', 'cancelledBy', User::class)->onlyOnDetail(),
            Textarea::make('Cancellation Reason')->onlyOnDetail(),

            BelongsTo::make('Created By', 'createdBy', User::class)->onlyOnDetail(),
            BelongsTo::make('Updated By', 'updatedBy', User::class)->onlyOnDetail(),
        ];
    }

    public function filters(Request $request)
    {
        return [
            new Filters\ReceiptStatus,
            new Filters\ReceiptPaymentMethod,
        ];
    }

    public function actions(Request $request)
    {
        return [
            new Actions\CancelReceipt,
        ];
    }
}
