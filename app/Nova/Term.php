<?php

namespace App\Nova;

use Davidpiesse\NovaToggle\Toggle;
use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Spatie\NovaTranslatable\Translatable;
use NovaErrorField\Errors ;

class Term extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Term';

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

    ];


    /**
     * Get the displayable label of the resource.
     *
     * @return  string
     */
    public static function label()
    {
        return __('Terms');
    }



    /**
     * Get the displayable singular label of the resource.
     *
     * @return  string
     */
    public static function singularLabel()
    {
        return __('Term');
    }


    /**
     * Get the fields displayed by the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [

            Errors::make(),
//            ID::make()->sortable(),
            Translatable::make([
                Text::make(__('Term Name'), 'name')->sortable()->rules('required'),
            ]),
            Select::make(__('Term Type') , 'type')->options([
                1 => __('Payment Voucher'),
                2 => __('Cash Receipt'),
                3 => __('Cash and Payment'),
            ])->displayUsingLabels()->rules('required'),
            Toggle::make(__('Status'), 'status')
                ->trueValue(1)
                ->falseValue(0)
                ->trueLabel(__('Active'))
                ->falseLabel(__('Inactive'))
                ->falseColor('#FF0000')
            ,
            Number::make(__('Display Order'), 'order')
                ->rules('required'),
        ];
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
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
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


    public function authorizedToDelete(Request $request)
    {
        return $this->deleteable;
    }
    
    public static function newModel()
    {
        $model = static::$model;
        $var = new $model;
        if(\Auth::check()) {
            $var->team_id = auth()->user()->current_team_id;
        }
        return $var;
    }

    
}
