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
use Surelab\Settings\ValueObjects\SettingRegister;

class OverOutDate extends Resource
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
        return __('OverOutDates');
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
        return __('OverOutDate');
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
            Text::make(__('Nights Count'), 'id')->displayUsing(function () {
                $reservation = $this;
                $date_in = Carbon::make($reservation->date_in);
                $date_out = Carbon::make($reservation->date_out);
                if(!is_null($date_out) && !is_null($date_in)){

                    $number_of_nights = $date_out->diff($date_in);
                    return $number_of_nights->days . ' ' . __('Night');
                }else{
                    return '-' . ' ' . __('Night');
                }

            }),

            Text::make(__('Date In'), 'date_in'),
            Text::make(__('Date Out'), 'date_out'),

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

            Text::make(__('From'), 'over_out_nights'),
        ];
    }

    /**
     * @param NovaRequest $request
     * @param Builder $query
     * @return Builder
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        $settingsDayEndTime = SettingRegister::getSettingItem('day_end')->getValue();
        $date = date('Y-m-d');
        // combined date is the current date combinted with day end time from settings
        $combinedDT = date('Y-m-d H:i:s', strtotime("$date $settingsDayEndTime"));
        $currentDate = Carbon::now('GMT+3')->format('Y-m-d') ;
//        dump($currentDate);
        $query->where('team_id', '=', \Auth::user()->current_team_id)
            ->where('team_id', '!=', null)
            ->whereNull('checked_out')
            ->whereNotNull('checked_in')
            ->where('date_out_time', '<=', $combinedDT)
            ->where('date_out' , '<' , $currentDate )
            ->where('status', '=', \App\Reservation::STATUS_CONFIRMED);
        return $query;
    }
}
