<?php

namespace App\Nova;
use App\Scopes\TeamScope;
use App\UnitCategory;
use Davidpiesse\NovaToggle\Toggle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Avatar;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use NovaErrorField\Errors ;
use Spatie\NovaTranslatable\Translatable;

class UnitSpecialFeature extends Resource
{
    public static $group = 'Units';
    const DEFAULT_INDEX_ORDER = 'order';

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\UnitSpecialFeature';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

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
        return __('Special Features');
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return  string
     */
    public static function singularLabel()
    {
        return __('Special Features');
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param Request $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [

            Errors::make(),
//            Avatar::make(__('Icon'), 'unit_feature_icon_id')
//                ->disk('public')
//                ->prunable(),
            Translatable::make([
                Text::make(__('Name'), 'name')->sortable()->rules('required'),
            ]),
            Toggle::make(__('Status'), 'status')
                ->trueValue(1)
                ->falseValue(0)
                ->trueLabel(__('Active'))
                ->falseLabel(__('Inactive'))
                ->falseColor('#FF0000')
            ,
            Number::make(__('Order'), 'order')
                ->rules('required'),
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

    public static function detailQuery(NovaRequest $request, $query)
    {
        $query->withGlobalScope('team_id', new TeamScope);
        return parent::detailQuery($request, $query);
    }
}
