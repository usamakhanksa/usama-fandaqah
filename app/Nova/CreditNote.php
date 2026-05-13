<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Status;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Http\Requests\NovaRequest;

class CreditNote extends Resource
{
    public static $model = \App\Models\CreditNote::class;

    public static $title = 'credit_note_number';

    public static $search = [
        'id', 'credit_note_number', 'zatca_uuid'
    ];

    public static function group()
    {
        return __('Finance');
    }

    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),

            BelongsTo::make(__('Team'), 'team', Team::class)->searchable(),

            Text::make(__('Credit Note Number'), 'credit_note_number')
                ->sortable()
                ->rules('required', 'max:255'),

            BelongsTo::make(__('Invoice'), 'invoice', Invoice::class)->searchable(),

            Date::make(__('Date'), 'credit_note_date')->sortable(),

            Badge::make(__('Reason'), 'reason')->map([
                'cancellation' => 'danger',
                'correction' => 'warning',
                'discount' => 'info',
                'partial_refund' => 'success',
                'other' => 'info',
            ])->labels([
                'cancellation' => __('Cancellation'),
                'correction' => __('Correction'),
                'discount' => __('Discount'),
                'partial_refund' => __('Partial Refund'),
                'other' => __('Other'),
            ]),

            Currency::make(__('Total Amount'), 'total_amount')->currency('SAR'),

            Badge::make(__('Status'), 'status')->map([
                'draft' => 'warning',
                'confirmed' => 'success',
                'cancelled' => 'danger',
            ]),

            Boolean::make(__('ZATCA Reported'), 'is_zatca_reported')->readonly(),

            Badge::make(__('ZATCA Status'), 'zatca_status')->map([
                'not_reported' => 'info',
                'pending' => 'warning',
                'reported' => 'success',
                'accepted' => 'success',
                'rejected' => 'danger',
                'error' => 'danger',
            ]),

            DateTime::make(__('ZATCA Submitted At'), 'zatca_submitted_at')->onlyOnDetail(),

            Text::make(__('ZATCA UUID'), 'zatca_uuid')->onlyOnDetail(),

            BelongsTo::make(__('Reservation'), 'reservation', Reservation::class)->nullable()->searchable(),
            BelongsTo::make(__('Guest'), 'guest', Guest::class)->nullable()->searchable(),
            BelongsTo::make(__('Company'), 'company', Company::class)->nullable()->searchable(),
            BelongsTo::make(__('Transaction'), 'transaction', Transaction::class)->nullable()->searchable(),

            HasMany::make(__('Items'), 'items', CreditNoteItem::class),
        ];
    }
}
