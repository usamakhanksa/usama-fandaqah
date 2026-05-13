<?php

namespace App\Nova;

use App\Models\CashierShift as ModelsCashierShift;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Http\Requests\NovaRequest;

class CashierShift extends Resource
{
    public static $model = ModelsCashierShift::class;

    public static $title = 'shift_number';

    public static $search = [
        'id', 'shift_number', 'notes', 'variance_reason'
    ];

    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),

            Text::make('Shift Number')->sortable(),

            BelongsTo::make('Team'),
            BelongsTo::make('Cashier', 'cashier', User::class),

            Badge::make('Status')->map([
                'open' => 'success',
                'closed' => 'warning',
                'pending_approval' => 'warning',
                'approved' => 'info',
                'rejected' => 'danger',
            ]),

            DateTime::make('Opened At')->sortable(),
            DateTime::make('Closed At')->sortable(),

            Currency::make('Opening Balance')->currency('SAR'),
            Currency::make('Expected Closing Balance', 'expected_closing_balance')->currency('SAR'),
            Currency::make('Actual Closing Balance', 'actual_closing_balance')->currency('SAR'),
            Currency::make('Variance')->currency('SAR'),

            Textarea::make('Variance Reason')->hideFromIndex(),
            Textarea::make('Notes'),

            BelongsTo::make('Approved By', 'approvedBy', User::class)->nullable()->hideFromIndex(),
            DateTime::make('Approved At')->hideFromIndex(),
            Textarea::make('Approval Notes')->hideFromIndex(),

            BelongsTo::make('Rejected By', 'rejectedBy', User::class)->nullable()->hideFromIndex(),
            DateTime::make('Rejected At')->hideFromIndex(),
            Textarea::make('Rejection Reason')->hideFromIndex(),

            Number::make('Total Transactions')->sortable(),

            HasMany::make('Transactions'),
        ];
    }

    public function filters(NovaRequest $request)
    {
        return [];
    }

    public function actions(NovaRequest $request)
    {
        return [];
    }
}
