<?php

namespace App\Nova;

use App\Models\Payment as PaymentModel;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Http\Requests\NovaRequest;

class Payment extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Payment::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'payment_number';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'payment_number', 'reference_number', 'cheque_number'
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),

            Text::make('Payment Number')
                ->sortable()
                ->readonly(),

            BelongsTo::make('Team'),

            BelongsTo::make('Reservation')->nullable(),

            BelongsTo::make('Guest')->nullable(),

            BelongsTo::make('Company')->nullable(),

            BelongsTo::make('Invoice')->nullable(),

            Date::make('Payment Date')->sortable(),

            Currency::make('Amount')
                ->currency('SAR')
                ->sortable(),

            Select::make('Currency')->options([
                'SAR' => 'SAR',
                'USD' => 'USD',
                'EUR' => 'EUR',
                'GBP' => 'GBP',
            ])->displayUsingLabels(),

            Number::make('Exchange Rate')->step(0.0001),

            Select::make('Payment Method')->options([
                'cash' => 'Cash',
                'visa' => 'Visa',
                'mastercard' => 'Mastercard',
                'mada' => 'Mada',
                'apple_pay' => 'Apple Pay',
                'bank_transfer' => 'Bank Transfer',
                'cheque' => 'Cheque',
                'online' => 'Online',
                'other' => 'Other',
            ])->displayUsingLabels()->sortable(),

            Select::make('Payment Type')->options([
                'deposit' => 'Deposit',
                'payment' => 'Payment',
                'partial_payment' => 'Partial Payment',
                'advance' => 'Advance',
                'refund' => 'Refund',
                'adjustment' => 'Adjustment',
            ])->displayUsingLabels()->sortable(),

            Text::make('Reference Number')->hideFromIndex(),

            Text::make('Bank Name')->hideFromIndex(),

            Text::make('Cheque Number')->hideFromIndex(),

            Text::make('Card Last Four')->hideFromIndex(),

            Select::make('Status')->options([
                'pending' => 'Pending',
                'confirmed' => 'Confirmed',
                'cancelled' => 'Cancelled',
                'refunded' => 'Refunded',
                'reversed' => 'Reversed',
            ])->displayUsingLabels()->sortable(),

            Boolean::make('Is Advance'),
            Boolean::make('Is Deposit'),

            Textarea::make('Description')->hideFromIndex(),
            Textarea::make('Notes')->hideFromIndex(),

            DateTime::make('Confirmed At')->onlyOnDetail(),
            BelongsTo::make('Confirmed By', 'confirmedBy', User::class)->onlyOnDetail(),

            DateTime::make('Cancelled At')->onlyOnDetail(),
            BelongsTo::make('Cancelled By', 'cancelledBy', User::class)->onlyOnDetail(),

            Textarea::make('Cancellation Reason')->onlyOnDetail(),

            BelongsTo::make('Transaction')->nullable()->hideFromIndex(),

            BelongsTo::make('Created By', 'createdBy', User::class)->onlyOnDetail(),
            BelongsTo::make('Updated By', 'updatedBy', User::class)->onlyOnDetail(),

            DateTime::make('Created At')->onlyOnDetail(),
            DateTime::make('Updated At')->onlyOnDetail(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function cards(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function filters(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function lenses(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return [];
    }
}
