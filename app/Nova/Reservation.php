<?php

namespace App\Nova;

use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\Panel;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\MorphMany;
use Laravel\Nova\Fields\Place;
use Laravel\Nova\Fields\Field;

class Reservation extends Resource
{
    public static $model = \App\Reservation::class;

    public static $title = 'number';

    public static $group = 'Booking Management';

    public static $ searchable = true;

    public static $search = [
        'id', 'number', 'status', 'reservation_category_type'
    ];

    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),

            Text::make('Number')->sortable()->readonly(function ($request) {
                return $request->isUpdateOrUpdateAttachedRequest();
            }),

            Select::make('Status')
                ->options([
                    'confirmed' => 'Confirmed',
                    'pending' => 'Pending',
                    'canceled' => 'Canceled',
                ])
                ->displayUsingLabels()
                ->sortable(),

            Select::make('Reservation Category Type', 'reservation_category_type')
                ->options([
                    'Normal' => 'Normal',
                    'Complimentary' => 'Complimentary',
                    'HouseUse' => 'House Use',
                    'DayUse' => 'Day Use',
                ])
                ->displayUsingLabels()
                ->sortable(),

            Textarea::make('Special Request'),

            Badge::make('Nights', function () {
                return $this->nights;
            })->labels([
                'low' => 'info',
                'medium' => 'success',
                'high' => 'warning',
            ])->map(function ($value) {
                if ($value < 3) {
                    return 'low';
                } elseif ($value < 7) {
                    return 'medium';
                } else {
                    return 'high';
                }
            }),
            
            // Customer & Company Section
            Panel::make('Customer & Company', [
                BelongsTo::make('Customer')->searchable(),
                BelongsTo::make('Company')->nullable()->searchable(),
            ])->collapsable(),

            // Unit & Dates Section
            Panel::make('Unit & Dates', [
                BelongsTo::make('Unit')->searchable(),
                Date::make('Date In', 'date_in')->required(),
                Date::make('Date Out', 'date_out')->required(),
                DateTime::make('Checked In', 'checked_in')->hideWhenCreating(),
                DateTime::make('Checked Out', 'checked_out')->hideWhenCreating(),
            ])->collapsable(),

            // Pricing Section
            Panel::make('Pricing', [
                Number::make('Total Price', 'total_price')->step(0.01)->displayUsing(function ($value) {
                    return $value ? '$' . number_format($value, 2) : 'N/A';
                }),
                Number::make('Sub Total', 'sub_total')->step(0.01)->displayUsing(function ($value) {
                    return $value ? '$' . number_format($value, 2) : 'N/A';
                }),
                Number::make('EWA Total', 'ewa_total')->step(0.01)->displayUsing(function ($value) {
                    return $value ? '$' . number_format($value, 2) : 'N/A';
                }),
                Number::make('VAT Total', 'vat_total')->step(0.01)->displayUsing(function ($value) {
                    return $value ? '$' . number_format($value, 2) : 'N/A';
                }),
            ])->collapsable(),

            // Booking Details
            Panel::make('Booking Details', [
                BelongsTo::make('Source')->nullable()->searchable(),
                BelongsTo::make('Created By', 'creator', 'App\Nova\User')->hideWhenCreating(),
                Text::make('Cancellation Reason')->onlyOnDetail(),
                Boolean::make('No-Show Flag', 'noshow_flag'),
                Text::make('Extension Reason')->onlyOnDetail(),
                Text::make('Shomoos Verification Status', 'shomoos_verification_status'),
            ])->collapsable(),

            // Timeline & Related Data
            Panel::make('Related Information', [
                Heading::make('Financial Information')->hideWhenCreating(),
                HasMany::make('Transactions', 'notPublicTransactions', 'App\Nova\Transaction')->hideWhenCreating(),
                HasMany::make('Invoices')->hideWhenCreating(),
                HasMany::make('Promissory')->hideWhenCreating(),
                
                Heading::make('Activity & Documents')->hideWhenCreating(),
                HasMany::make('Reservation Transfers', 'reservationTransfers', 'App\Nova\ReservationTransfer')->hideWhenCreating(),
                HasMany::make('Signed Contracts', 'signedContracts', 'App\Nova\ReservationContract')->hideWhenCreating(),
                HasMany::make('Ratings')->hideWhenCreating(),
                MorphMany::make('Activities', 'activities', 'App\Nova\Activity')->hideWhenCreating(),
                MorphMany::make('Comments')->hideWhenCreating(),
            ])->collapsable(),
        ];
    }

    public function cards(NovaRequest $request)
    {
        return [];
    }

    public function filters(NovaRequest $request)
    {
        return [
            new Filters\ReservationStatusFilter,
            new Filters\ReservationCategoryTypeFilter,
            new Filters\ReservationDateFilter,
            new Filters\ReservationCustomerFilter,
            new Filters\ReservationUnitFilter,
        ];
    }

    public function lenses(NovaRequest $request)
    {
        return [];
    }

    public function actions(NovaRequest $request)
    {
        return [
            new Actions\ExportReservations,
            new Actions\SendCheckInReminder,
            new Actions\MarkAsNoShow,
            new Actions\BulkCheckIn,
            new Actions\BulkCancel,
        ];
    }
}