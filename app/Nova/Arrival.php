<?php


namespace App\Nova;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Inspheric\Fields\Indicator;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Status;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Nova\Lenses\UnitsMovementReport;

class Arrival extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Reservation';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'number';

    /**
     * Get the displayable label of the resource.
     *
     * @return  string
     */
    public static function label()
    {
        return __('Arrivals');
    }

    public static function authorizedToCreate(Request $request)
    {
        return false;
    }

    public function authorizedToDelete(Request $request)
    {
        return false;
    }

    public function authorizedToForceDelete(Request $request)
    {
        return false;
    }

    public function authorizedToUpdate(Request $request)
    {
        return false;
    }

    public static function searchable()
    {
        return false;
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return  string
     */
    public static function singularLabel()
    {
        return __('Arrival');
    }

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'number',
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
            ID::make(__('ID'), 'id')->sortable()->hideFromIndex(),
            Text::make(__('Reservation Number'), 'number')->displayUsing(function () {
                return '#' . $this->number;
            })->sortable()->onlyOnIndex(),
            BelongsTo::make(__('Customer'), 'customer', Customer::class)->rules('required'),
            BelongsTo::make(__('Unit Number'), 'unit', Unit::class)->display('unit_number'),
            Text::make(__('Nights Count'), 'nights'),

            Text::make(__('Reservation Status'), 'id')->displayUsing(function ($reservation) {
                if (is_null($this->checked_in) && is_null($this->checked_out)) {
                    $label = __('Pending');
                    $class = 'text-info';
                } else if (!is_null($this->checked_in) && is_null($this->checked_out)) {
                    $label = __('Checked In');
                    $class = 'text-green-500';
                } else {
                    $label = __('Checked Out');
                    $class = 'text-red-500';
                }
                return '<div class="font-bold "><b class="' . $class . '">' . $label . '</b></div>';
            })->asHtml()->onlyOnIndex(),

            Text::make(__('Date In'), 'date_in'),

            Currency::make(__('Total Credit'), 'total_price')->displayUsing(function () {
                $reservation = $this;
                $reservation->wallet->refreshBalance();
                // i need balance to find debit and credit
                $balance = $reservation->balance / 100;
                if ($balance > 0) {
                    $label = '(' . __('credit') . ')';
                    $class = 'text-success';
                } elseif ($balance < 0) {
                    $label = '(' . __('debit') . ')';
                    $class = 'text-danger';
                } else {
                    $label = '';
                    $class = 'text-black';
                }

                $balance = abs($balance);
                return '<div class="font-bold ' . $class . '"><b>' . $balance . ' ' . __('SAR') . '</b><span class="' . $class . '">  ' . $label . ' </span></div>';
            })->asHtml(),
        ];
    }

    /**
     * @param NovaRequest $request
     * @param Builder $query
     * @return Builder
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        $query->where('team_id', '=', \Auth::user()->current_team_id)
            ->where('team_id', '!=',null)
            ->where('status', '=', \App\Reservation::STATUS_CONFIRMED)
            ->where('date_in', '=', $request->get('date'))
        ;

        return $query;
    }
}
