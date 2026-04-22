<?php

namespace App\Nova\Foundation;

use App\Models\Foundation\Reservation as ReservationModel;
use App\Nova\Actions\Foundation\MarkReservationConfirmed;
use App\Nova\Filters\Foundation\ReservationStatusFilter;
use App\Nova\Lenses\Foundation\HighValueReservations;
use App\Nova\Metrics\Foundation\ReservationsPerDay;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class Reservation extends \Laravel\Nova\Resource
{
    public static $model = ReservationModel::class;

    public static $title = 'reservation_no';

    public static $search = ['id', 'reservation_no', 'source'];

    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),
            Text::make('Reservation No', 'reservation_no')->readonly(),
            BelongsTo::make('Profile', 'customer', Customer::class),
            BelongsTo::make('Unit', 'unit', Unit::class),
            Date::make('Check In', 'check_in_date'),
            Date::make('Check Out', 'check_out_date'),
            Select::make('Status')->options([
                'pending' => 'Pending',
                'confirmed' => 'Confirmed',
                'checked_in' => 'Checked In',
                'checked_out' => 'Checked Out',
                'cancelled' => 'Cancelled',
            ])->displayUsingLabels(),
            Number::make('Night Rate SAR', 'night_rate_sar')->step(0.01),
            Number::make('Total SAR', 'total_sar')->step(0.01)->readonly(),
        ];
    }

    public function cards(NovaRequest $request): array
    {
        return [new ReservationsPerDay()];
    }

    public function filters(NovaRequest $request): array
    {
        return [new ReservationStatusFilter()];
    }

    public function lenses(NovaRequest $request): array
    {
        return [new HighValueReservations()];
    }

    public function actions(NovaRequest $request): array
    {
        return [new MarkReservationConfirmed()];
    }
}
