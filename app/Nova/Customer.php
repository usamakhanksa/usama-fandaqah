<?php

namespace App\Nova;

use Carbon\Carbon;
use NovaErrorField\Errors ;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\Text;
use Surelab\TelInput\TelInput;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\BelongsTo;
use LaravelNovaFields\Gender\Gender;
use App\Nova\Filters\CustomerHighlight;
use Surelab\CustomerNotes\CustomerNotes;
use Laravel\Nova\Http\Requests\NovaRequest;
use Sloveniangooner\SearchableSelect\SearchableSelect;
use Surelab\CustomDate\CustomDate;

class Customer extends Resource
{

    public static $displayInNavigation = false;

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Customer';
    public static $group = null;

    /**
     * Get the displayable label of the resource.
     *
     * @return  string
     */
    public static function label()
    {
        return __('Customers');
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return  string
     */
    public static function singularLabel()
    {
        return __('Customer');
    }

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'name', 'email', 'phone'
    ];


    public static function indexQuery(NovaRequest $request, $query)
    {
        $query->where('team_id', '=', \Auth::user()->current_team_id);
        return $query;
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function fields(Request $request)
    {
        $today = Carbon::today()->format('Y-m-d');

        return [

            Errors::make(),

            Text::make(__('Name'), 'name')
                ->creationRules('required'),

            Text::make(__('Highlight'), 'label')
                ->asHtml()
                ->exceptOnForms(),

            BelongsTo::make(__('Highlight'), 'highlight', Highlight::class)->nullable()->onlyOnForms(),

            Text::make(__('ID Number') , 'id_number')->onlyOnIndex() ,

            Text::make(__('Email'), 'email'),

            // this is a little bit tricky see this  : https://github.com/laravel/nova-issues/issues/179
            HasMany::make(  __('The Reservations')  , 'reservations' , \App\Nova\Reservation::class)->onlyOnDetail(),
            Text::make(__('The Reservations') , 'reservations_count')->displayUsing(function($count){
                return '<a href="" data-attr-id='.$this->id.' class="no-underline dim text-primary font-bold view_customer">' .
                    $count . '</a>';
            })->onlyOnIndex()->asHtml(),

            TelInput::make(__('Customer Phone'), 'phone')
                ->rules('required','numeric','min:99999999')
                ->preferredCountries('SA')
                ->defaultCountry('SA')
                ->creationRules('required','unique:customer,phone')
                ->updateRules('required','unique:customer,phone,{{resourceId}}'),

            Gender::make(__('Gender'), 'gender')->hideFromIndex(),

            Select::make(__('Customer Type'), 'customer_type')->options(\App\Customer::CustomerTypes())->displayUsingLabels()->hideFromIndex(),

            BelongsTo::make(__('Nationality'), 'nationality', Country::class)->viewable(false),
            // SearchableSelect::make(__('Nationality'), "nationality")
            //             ->resource(Country::class)
            //             ->displayUsingLabels(),
            Select::make(__('Id Type'), 'id_type')->options(\App\Customer::idTypes()->keyBy('id')->map(function ($name) {
            return $name['title'];
        }))->displayUsingLabels()->hideFromIndex(),
            Text::make(__('Id Number'), 'id_number')
            ->creationRules('numeric','unique:customer,id_number')
            ->updateRules('numeric','unique:customer,id_number,{{resourceId}}')
            ->hideFromIndex(),
            Text::make(__('Work'), 'work')->hideFromIndex(),
            Text::make(__('Work Phone'), 'work_phone')->hideFromIndex(),
            Text::make(__('Address'), 'address')->hideFromIndex(),

            CustomDate::make(__('Id Expire Date'), 'id_expire_date')->hideFromIndex(),
            CustomDate::make(__('Birthday Date'), 'birthday_date')->hideFromIndex(),

            // Date::make(__('Id Expire Date'), 'id_expire_date')->hideFromIndex(),
            // Date::make(__('Birthday Date'), 'birthday_date')->withMeta(['value' => $this->checkmydate($this->birthday_date)  ? Carbon::parse($this->birthday_date)->format('Y-m-d') : Carbon::now()->subDays(10)->toDateString()])
            //     ->creationRules('required' , 'date' , 'before_or_equal:' . $today)
            //     ->hideFromIndex()
            //     ->hideFromDetail()
            //     ->hideWhenCreating()
            //     ->hideWhenUpdating(),


            CustomerNotes::make()


        ];
    }

    function checkmydate($date) {
        $tempDate = explode('-', $date);



        if($tempDate[0] == ""){
            return false;
        }
//         dd(checkdate($tempDate[1], $tempDate[2], $tempDate[0]));
        return checkdate((int) $tempDate[1], (int) $tempDate[2], (int) $tempDate[0]);
    }
    /**
     * Get the cards available for the request.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [
            new Filters\GuestsReport\CustomerName(),
            new Filters\GuestsReport\CustomerIdNumber(),
            new CustomerHighlight(),
//            new Filters\GuestsReport\CustomerRegistrationDate(),
            new Filters\GuestsReport\PhoneNumberFilter(),
            new Filters\GuestsReport\GenderFilter(),

        ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [

            // (new Lenses\GuestsReport(static::newModel()))
        ];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }

    /**
     * Get a fresh instance of the model represented by the resource.
     *
     * @return mixed
     */
    public static function newModel()
    {
        $model = static::$model;
        $var = new $model;
        $var->team_id = auth()->user()->current_team_id;
        return $var;
    }


    public static function softDeletes()
    {
        return false ;
    }


    public static function searchable()
    {
        return false;
    }


    public function authorizedToDelete(Request $request)
    {
        return !$this->reservations->count();
    }
}
