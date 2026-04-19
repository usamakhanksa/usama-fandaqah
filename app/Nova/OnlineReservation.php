<?php

namespace App\Nova;

use App\Nova\Filters\Reservations\CustomerName;
use App\Nova\Filters\Reservations\DateInFilter;
use App\Nova\Filters\Reservations\ReservationNumber;
use App\Nova\Filters\Reservations\UnitNumber;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Date;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Status;
use Laravel\Nova\Fields\Text;
use LaravelNovaFields\Gender\Gender;
use SureLab\OnlineReservation\OnlineReservationCard;
use SureLab\OnlineReservation\OnlineReservationStatus;
use SureLab\OnlineReservation\ViewOnlineReservation;
use Surelab\TelInput\TelInput;
use App\Nova\Filters\TransactionDateRange;

class OnlineReservation extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\OnlineReservation';

    /**
     * Get the displayable label of the resource.
     *
     * @return  string
     */
    public static function label()
    {
        return __('Online Reservations');
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return  string
     */
    public static function singularLabel()
    {
        return __('Online Reservation');
    }

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'number';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'number'
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            Number::make(__('Reservation Number'), 'number')->displayUsing(function ($number) {
                return '#' . $number;
            }),
            BelongsTo::make(__('Customer'), 'customer', Customer::class),
            BelongsTo::make(__('Unit Number'), 'unit', Unit::class)->display('unit_number'),
            OnlineReservationStatus::make($this, __('Status')),
            Date::make(__('Date In') ,'date_in'),
            Date::make(__('Date Out') ,'date_out'),
            Number::make(__('Nights'), 'nights')->displayUsing(function ($nights) {
                return $nights . ' ' . __('Nights');
            }),
            Number::make(__('Price'), 'price')->displayUsing(function ($price) {
                return $price . ' ' . __('SAR');
            }),
            ViewOnlineReservation::make($this)
        ];
    }

    public function cards(Request $request)
    {
        return [
            (new OnlineReservationCard)->waiting($request)->cancelled($request)->confirmed($request)->total($request),
        ];
    }

    public function filters(Request $request)
    {
        return [
            new DateInFilter,
            new CustomerName,
            new ReservationNumber,
            new UnitNumber
        ];
    }

    public function authorizedToView(Request $request)
    {
        return false;
    }

    public static function authorizedToCreate(Request $request)
    {
        return false;
    }

    public function authorizedToDelete(Request $request)
    {
        return false;
    }

    public function authorizedToUpdate(Request $request)
    {
        return false;
    }
}
