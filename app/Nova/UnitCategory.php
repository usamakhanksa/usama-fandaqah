<?php

namespace App\Nova;
use Laravel\Nova\Panel;
use R64\NovaFields\JSON;
use App\Scopes\TeamScope;
use Manogi\Tiptap\Tiptap;
use NovaErrorField\Errors;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Trix;
use Inspheric\Fields\Indicator;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Surlab\UnitPrice\UnitPrice;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Textarea;
use Davidpiesse\NovaToggle\Toggle;
use Surelab\NovaEditorV2\NovaEditorV2;
use Media24si\NovaYoutubeField\Youtube;
use Fourstacks\NovaCheckboxes\Checkboxes;
use Spatie\NovaTranslatable\Translatable;
use Laravel\Nova\Http\Requests\NovaRequest;
use Ebess\AdvancedNovaMediaLibrary\Fields\Media;
use Ebess\AdvancedNovaMediaLibrary\Fields\Images;

class UnitCategory extends Resource
{
    public static $group = 'Units';
    const DEFAULT_INDEX_ORDER = 'order';

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\UnitCategory::class;

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
    public static $search = [];

    /**
     * Get the displayable label of the resource.
     *
     * @return  string
     */
    public static function label()
    {
        return __('Units Category');
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return  string
     */
    public static function singularLabel()
    {
        return __('Units Category');
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param Request $request
     * @return array
     */
    public function fields(Request $request)
    {
        $currentPlan = auth()->user()->currentTeam()->last_subscription;
        $upload_file_size = env('MAX_UPLOAD_SIZE', 1048576) / 1024;
        $currency = getCurrency();
        if(!$currency){
            $currency = 'SAR';
        }
        return [
            Errors::make(),
            new Panel(__('Main Info'), [
                Heading::make(__('Unit Category Information')),

                Select::make(__('Unit Type'), 'type_id')->options(\App\UnitCategory::types())->displayUsingLabels()->rules('required')->hideFromIndex(),

                Translatable::make([
                    Text::make(__('Name'), 'name')->sortable()->rules('required'),
                ]),
                Number::make(__('Unit Size (m²)'), 'unit_size')
                    ->rules('nullable', 'numeric', 'min:0.1')
                    ->creationRules('nullable', 'numeric', 'min:0.1')
                    ->updateRules('nullable', 'numeric', 'min:0.1')
                    ->withMeta([
                        'extraAttributes' => [
                            'step' => '0.01', // Allows decimal steps in input
                        ],
                    ])
                    ->help(__('Unit size must be greater than 0.')),

                Number::make(__('Number of Adults'), 'number_of_adults')
                    ->rules('nullable', 'integer', 'min:0')
                    ->help(__('Enter a maximum number of adults.')), // Ensures non-negative integer

                Number::make(__('Number of Children'), 'number_of_children')
                    ->rules('nullable', 'integer', 'min:0')
                    ->help(__('Enter maximum number of children.')), // Ensures non-negative integer

                Number::make(__('Number of Beds'), 'number_of_beds')
                    ->rules('nullable', 'integer', 'min:0')
                    ->help(__('Enter number of beds.')), // Ensures non-negative integer

                Toggle::make(__('Status'), 'status')
                    ->trueValue(1)
                    ->falseValue(0)
                    ->trueLabel(__('Active'))
                    ->falseLabel(__('Inactive'))
                    ->falseColor('#FF0000'),

                    Toggle::make(__('enable staah pricing'), 'enable_staah_pricing')
                    ->trueValue(1)
                    ->falseValue(0)
                    ->trueLabel(__('Active'))
                    ->falseLabel(__('Inactive'))
                    ->falseColor('#FF0000'),

                Toggle::make(__('Show in website'), 'show_in_website')
                    ->trueValue(1)
                    ->falseValue(0)
                    ->trueLabel(__('Active'))
                    ->falseLabel(__('Inactive'))
                    ->falseColor('#FF0000')
            ]),



            !($currentPlan && $currentPlan->stripe_plan == 'monthly-yearly') ? new Panel(__('Daily Prices'), [
                Heading::make(__('Daily Prices :  will be inherited in unit creation, and can be modified in unit')),

                // Currency::make(__('Sunday Day Price'), 'sunday_day_price')
                // ->rules('required')
                // ->min(0)
                // ->format('SAR %!n')
                // ->hideFromIndex(),
                UnitPrice::make(__('Sunday Day Price'), 'sunday_day_price')
                    ->thousandsSeparator(',')
                    ->prefix('SAR ')
                    ->decimals(5)
                    ->rules('required', 'numeric', 'min:1')->hideFromIndex()->hideFromDetail(),
                Currency::make(__('Min Sunday Day Price'), 'min_sunday_day_price')
                    ->min(0)
                    ->format('SAR %!n')
                    ->hideFromIndex()->hideFromDetail(),

                    Text::make(__('Sunday Day Price'), 'sunday_day_price')->displayUsing(function ($val) use($currency) {
                        $symbol = null;
                        if($currency == 'SAR'){
                            $symbol = '<i class="icon-saudi_riyal"></i>';
                        }else{
                            $symbol = '<i>'. __($currency) .'</i>';
                        }
                        return '<p class="p.text-90">'. number_format($val,5) . ' ' . $symbol .'  <p>';
                    })->asHtml()->onlyOnDetail(),    
    
                    Text::make(__('Min Sunday Day Price'), 'min_sunday_day_price')->displayUsing(function ($val) use($currency) {
                        $symbol = null;
                        if($currency == 'SAR'){
                            $symbol = '<i class="icon-saudi_riyal"></i>';
                        }else{
                            $symbol = '<i>'. __($currency) .'</i>';
                        }
                        return '<p class="p.text-90">'. number_format($val,5) . ' ' . $symbol .'  <p>';
                    })->asHtml()->onlyOnDetail(), 

                // Currency::make(__('Monday Day Price'), 'monday_day_price')
                // ->rules('required')
                // ->min(0)
                // ->format('SAR %!n')
                // ->hideFromIndex(),

                UnitPrice::make(__('Monday Day Price'), 'monday_day_price')
                    ->thousandsSeparator(',')
                    ->prefix('SAR ')
                    ->decimals(5)
                    ->rules('required', 'numeric', 'min:1')->hideFromIndex()->hideFromDetail(),
                Currency::make(__('Min Monday Day Price'), 'min_monday_day_price')
                    ->min(0)
                    ->format('SAR %!n')
                    ->hideFromIndex()->hideFromDetail(),

                    Text::make(__('Monday Day Price'), 'monday_day_price')->displayUsing(function ($val) use($currency) {
                        $symbol = null;
                        if($currency == 'SAR'){
                            $symbol = '<i class="icon-saudi_riyal"></i>';
                        }else{
                            $symbol = '<i>'. __($currency) .'</i>';
                        }
                        return '<p class="p.text-90">'. number_format($val,5) . ' ' . $symbol .'  <p>';
                    })->asHtml()->onlyOnDetail(),    
                    
                    Text::make(__('Min Monday Day Price'), 'min_monday_day_price')->displayUsing(function ($val) use($currency) {
                        $symbol = null;
                        if($currency == 'SAR'){
                            $symbol = '<i class="icon-saudi_riyal"></i>';
                        }else{
                            $symbol = '<i>'. __($currency) .'</i>';
                        }
                        return '<p class="p.text-90">'. number_format($val,5) . ' ' . $symbol .'  <p>';
                    })->asHtml()->onlyOnDetail(),   

                // Currency::make(__('Tuesday Day Price'), 'tuesday_day_price')
                // ->min(0)
                // ->rules('required')
                // ->format('SAR %!n')
                // ->hideFromIndex(),

                UnitPrice::make(__('Tuesday Day Price'), 'tuesday_day_price')
                    ->thousandsSeparator(',')
                    ->prefix('SAR ')
                    ->decimals(5)
                    ->rules('required', 'numeric', 'min:1')->hideFromIndex()->hideFromDetail(),
                Currency::make(__('Min Tuesday Day Price'), 'min_tuesday_day_price')
                    ->min(0)
                    ->format('SAR %!n')
                    ->hideFromIndex()->hideFromDetail(),

                    Text::make(__('Tuesday Day Price'), 'tuesday_day_price')->displayUsing(function ($val) use($currency) {
                        $symbol = null;
                        if($currency == 'SAR'){
                            $symbol = '<i class="icon-saudi_riyal"></i>';
                        }else{
                            $symbol = '<i>'. __($currency) .'</i>';
                        }
                        return '<p class="p.text-90">'. number_format($val,5) . ' ' . $symbol .'  <p>';
                    })->asHtml()->onlyOnDetail(),    
                    
                    Text::make(__('Min Tuesday Day Price'), 'min_tuesday_day_price')->displayUsing(function ($val) use($currency) {
                        $symbol = null;
                        if($currency == 'SAR'){
                            $symbol = '<i class="icon-saudi_riyal"></i>';
                        }else{
                            $symbol = '<i>'. __($currency) .'</i>';
                        }
                        return '<p class="p.text-90">'. number_format($val,5) . ' ' . $symbol .'  <p>';
                    })->asHtml()->onlyOnDetail(),

                // Currency::make(__('Wednesday Day Price'), 'wednesday_day_price')
                // ->min(0)
                // ->rules('required')
                // ->format('SAR %!n')
                // ->hideFromIndex(),
                UnitPrice::make(__('Wednesday Day Price'), 'wednesday_day_price')
                    ->thousandsSeparator(',')
                    ->prefix('SAR ')
                    ->decimals(5)
                    ->rules('required', 'numeric', 'min:1')->hideFromIndex()->hideFromDetail(),
                Currency::make(__('Min Wednesday Day Price'), 'min_wednesday_day_price')
                    ->min(0)
                    ->format('SAR %!n')
                    ->hideFromIndex()->hideFromDetail(),

                    Text::make(__('Wednesday Day Price'), 'wednesday_day_price')->displayUsing(function ($val) use($currency) {
                        $symbol = null;
                        if($currency == 'SAR'){
                            $symbol = '<i class="icon-saudi_riyal"></i>';
                        }else{
                            $symbol = '<i>'. __($currency) .'</i>';
                        }
                        return '<p class="p.text-90">'. number_format($val,5) . ' ' . $symbol .'  <p>';
                    })->asHtml()->onlyOnDetail(),    
                    
                    Text::make(__('Min Wednesday Day Price'), 'min_wednesday_day_price')->displayUsing(function ($val) use($currency) {
                        $symbol = null;
                        if($currency == 'SAR'){
                            $symbol = '<i class="icon-saudi_riyal"></i>';
                        }else{
                            $symbol = '<i>'. __($currency) .'</i>';
                        }
                        return '<p class="p.text-90">'. number_format($val,5) . ' ' . $symbol .'  <p>';
                    })->asHtml()->onlyOnDetail(), 

                // Currency::make(__('Thursday Day Price'), 'thursday_day_price')
                // ->min(0)
                // ->rules('required')
                // ->format('SAR %!n')
                // ->hideFromIndex(),

                UnitPrice::make(__('Thursday Day Price'), 'thursday_day_price')
                    ->thousandsSeparator(',')
                    ->prefix('SAR ')
                    ->decimals(5)
                    ->rules('required', 'numeric', 'min:1')->hideFromIndex()->hideFromDetail(),
                Currency::make(__('Min Thursday Day Price'), 'min_thursday_day_price')
                    ->min(0)
                    ->format('SAR %!n')
                    ->hideFromIndex()->hideFromDetail(),

                    Text::make(__('Thursday Day Price'), 'thursday_day_price')->displayUsing(function ($val) use($currency) {
                        $symbol = null;
                        if($currency == 'SAR'){
                            $symbol = '<i class="icon-saudi_riyal"></i>';
                        }else{
                            $symbol = '<i>'. __($currency) .'</i>';
                        }
                        return '<p class="p.text-90">'. number_format($val,5) . ' ' . $symbol .'  <p>';
                    })->asHtml()->onlyOnDetail(),    
                    
                    Text::make(__('Min Thursday Day Price'), 'min_thursday_day_price')->displayUsing(function ($val) use($currency) {
                        $symbol = null;
                        if($currency == 'SAR'){
                            $symbol = '<i class="icon-saudi_riyal"></i>';
                        }else{
                            $symbol = '<i>'. __($currency) .'</i>';
                        }
                        return '<p class="p.text-90">'. number_format($val,5) . ' ' . $symbol .'  <p>';
                    })->asHtml()->onlyOnDetail(),

                // Currency::make(__('Friday Day Price'), 'friday_day_price')
                // ->min(0)
                // ->rules('required')
                // ->format('SAR %!n')->hideFromIndex(),

                UnitPrice::make(__('Friday Day Price'), 'friday_day_price')
                    ->thousandsSeparator(',')
                    ->prefix('SAR ')
                    ->decimals(5)
                    ->rules('required', 'numeric', 'min:1')->hideFromIndex()->hideFromDetail(),
                Currency::make(__('Min Friday Day Price'), 'min_friday_day_price')
                    ->min(0)
                    ->format('SAR %!n')
                    ->hideFromIndex()->hideFromDetail(),

                    Text::make(__('Friday Day Price'), 'friday_day_price')->displayUsing(function ($val) use($currency) {
                        $symbol = null;
                        if($currency == 'SAR'){
                            $symbol = '<i class="icon-saudi_riyal"></i>';
                        }else{
                            $symbol = '<i>'. __($currency) .'</i>';
                        }
                        return '<p class="p.text-90">'. number_format($val,5) . ' ' . $symbol .'  <p>';
                    })->asHtml()->onlyOnDetail(),    
                    
                    Text::make(__('Min Friday Day Price'), 'min_friday_day_price')->displayUsing(function ($val) use($currency) {
                        $symbol = null;
                        if($currency == 'SAR'){
                            $symbol = '<i class="icon-saudi_riyal"></i>';
                        }else{
                            $symbol = '<i>'. __($currency) .'</i>';
                        }
                        return '<p class="p.text-90">'. number_format($val,5) . ' ' . $symbol .'  <p>';
                    })->asHtml()->onlyOnDetail(),

                // Currency::make(__('Saturday Day Price'), 'saturday_day_price')
                // ->min(0)
                // ->rules('required')
                // ->format('SAR %!n')
                // ->hideFromIndex(),

                UnitPrice::make(__('Saturday Day Price'), 'saturday_day_price')
                    ->thousandsSeparator(',')
                    ->prefix('SAR ')
                    ->decimals(5)
                    ->rules('required', 'numeric', 'min:1')->hideFromIndex()->hideFromDetail(),
                Currency::make(__('Min Saturday Day Price'), 'min_saturday_day_price')
                    ->min(0)
                    ->format('SAR %!n')
                    ->hideFromIndex()->hideFromDetail(),
                    Text::make(__('Saturday Day Price'), 'saturday_day_price')->displayUsing(function ($val) use($currency) {
                        $symbol = null;
                        if($currency == 'SAR'){
                            $symbol = '<i class="icon-saudi_riyal"></i>';
                        }else{
                            $symbol = '<i>'. __($currency) .'</i>';
                        }
                        return '<p class="p.text-90">'. number_format($val,5) . ' ' . $symbol .'  <p>';
                    })->asHtml()->onlyOnDetail(),    
                    
                    Text::make(__('Min Saturday Day Price'), 'min_saturday_day_price')->displayUsing(function ($val) use($currency) {
                        $symbol = null;
                        if($currency == 'SAR'){
                            $symbol = '<i class="icon-saudi_riyal"></i>';
                        }else{
                            $symbol = '<i>'. __($currency) .'</i>';
                        }
                        return '<p class="p.text-90">'. number_format($val,5) . ' ' . $symbol .'  <p>';
                    })->asHtml()->onlyOnDetail(),

            ]) : new Panel(__('Old is gold'), []),


            new Panel(__('Monthly Prices'), [

                Heading::make(__('Monthly Price :  will be inherited in unit creation, and can be modified in unit')),

                // Currency::make(__('Month Price'), 'month_price')->min(0)->rules('required')->format('SAR %!n')->hideFromIndex(),
                UnitPrice::make(__('Month Price'), 'month_price')
                    ->thousandsSeparator(',')
                    ->prefix('SAR ')
                    ->decimals(5)
                    ->rules('required', 'numeric', 'min:1')
                    ->hideFromIndex()
                    ->hideFromDetail(),
                Currency::make(__('Min Month Price'), 'min_month_price')->min(0)->format('SAR %!n')->hideFromIndex()->hideFromDetail(),

                Text::make(__('Month Price'), 'month_price')->displayUsing(function ($val) use($currency) {
                    $symbol = null;
                    if($currency == 'SAR'){
                        $symbol = '<i class="icon-saudi_riyal"></i>';
                    }else{
                        $symbol = '<i>'. __($currency) .'</i>';
                    }
                    return '<p class="p.text-90">'. number_format($val,5) . ' ' . $symbol .'  <p>';
                })->asHtml()->onlyOnDetail(),    
                
                Text::make(__('Min Month Price'), 'min_month_price')->displayUsing(function ($val) use($currency) {
                    $symbol = null;
                    if($currency == 'SAR'){
                        $symbol = '<i class="icon-saudi_riyal"></i>';
                    }else{
                        $symbol = '<i>'. __($currency) .'</i>';
                    }
                    return '<p class="p.text-90">'. number_format($val,5) . ' ' . $symbol .'  <p>';
                })->asHtml()->onlyOnDetail(),

            ]),


            //            new Panel(__('Hourly Prices'), [
            //
            //                Heading::make(__('Hourly Price :  will be inherited in unit creation, and can be modified in unit')),
            //
            //                Currency::make(__('Hour Price'), 'hour_price')->rules('required')->format('SAR %!n')->hideFromIndex(),
            //
            //
            //            ]),

            /** ---------------------- old is gold -------------------------------------------------- */
            //            new Panel(__('General Features'), [
            //                Heading::make(__('General Features for units')),
            //                Checkboxes::make(__('General Features'), 'general_features')
            //                    ->options(\App\UnitGeneralFeature::all()->pluck('name', 'id'))
            //                    ->saveAsString()
            //                    ->saveUncheckedValues()
            //                    ->displayUncheckedValuesOnIndex()
            //                    ->displayUncheckedValuesOnDetail()
            //                    ->hideFromIndex(),
            //
            //            ]),
            //            new Panel(__('Special Features'), [
            //                Heading::make(__('Special Features for units')),
            //                Checkboxes::make(__('Special Features'), 'special_features')
            //                    ->options(\App\UnitSpecialFeature::all()->pluck('name', 'id'))
            //                    ->saveAsString()
            //                    ->saveUncheckedValues()
            //                    ->displayUncheckedValuesOnIndex()
            //                    ->displayUncheckedValuesOnDetail()
            //                    ->hideFromIndex(),
            //
            //            ])
            /** ---------------------- old is gold -------------------------------------------------- */

            /** ---------------------------- new feature ------------------------------------------- */

            new Panel(__('Features'), [
                Heading::make(__('Features Optional for website')),

                Checkboxes::make(__('Special Features'), 'special_features')
                    ->options(\App\UnitSpecialFeature::where('status', 1)->get()->pluck('name', 'id')->toArray())
                    ->saveAsString()
                    ->saveUncheckedValues()
                    ->displayUncheckedValuesOnIndex()
                    ->displayUncheckedValuesOnDetail()
                    ->hideFromIndex(),


                Checkboxes::make(__('General Features'), 'general_features')
                    ->options(\App\UnitGeneralFeature::where('status', 1)->get()->pluck('name', 'id')->toArray())
                    ->saveAsString()
                    ->saveUncheckedValues()
                    ->displayUncheckedValuesOnIndex()
                    ->displayUncheckedValuesOnDetail()
                    ->hideFromIndex(),

                //                JSON::make('Content', $this->OptionList(), 'unit_options')
                //
                //                    ->hideLabelInForms()
                //                    ->hideLabelInDetail()
                //                    ->wrapperClasses('')
                //                    ->fieldClasses('units-options')
                //                ,

                Images::make(__('Main Image'), 'main')
                    ->singleMediaRules('max:' . $upload_file_size),
                Images::make(__('Unit Images'), 'images')->hideFromIndex()
                    ->singleMediaRules('max:' . $upload_file_size),


                // Media::make(__('Video'), 'video')
                //     ->hideFromIndex()
                //     ->conversionOnIndexView('thumb')
                //     ->singleMediaRules('max:15000')->hideFromIndex(),

                Youtube::make(__('Youtube'), 'youtube_link')->hideFromIndex(),

                Translatable::make([
                    NovaEditorV2::make(__('Description'), 'description')->hideFromIndex(),
                    Textarea::make(__('Short Description'), 'short_description')->withMeta([
                        'extraAttributes' => [
                            'style' => 'width : 60% !important;'
                        ]
                    ])->hideFromIndex()
                    // Trix::make(__('Short Description'), 'short_description')->hideFromIndex(),
                ]),
            ]),

            /** ---------------------------- new feature ------------------------------------------- */
        ];
    }


    /**
     * Get the cards available for the request.
     *
     * @param Request $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param Request $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param Request $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param Request $request
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
        $var->status = 1;
        return $var;
    }

    //    protected function OptionList()
    //    {
    //        $data = [];
    //        $options = \App\UnitOption::all()->toArray();
    //
    //        foreach ($options as $option) {
    //            $data[] = Text::make($option['name'][App::getLocale()]);
    //        }
    //        return $data;
    //    }

    public static function softDeletes()
    {
        return false;
    }

    public function authorizedToDelete(Request $request)
    {
        return false;
    }

    public static function detailQuery(NovaRequest $request, $query)
    {
        $query->withGlobalScope('team_id', new TeamScope);
        return parent::detailQuery($request, $query);
    }

    public static function scoutQuery(NovaRequest $request, $query)
    {
        $query->withGlobalScope('team_id', new TeamScope);
        return parent::scoutQuery($request, $query);
    }
}
