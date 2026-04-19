<?php

namespace App\Nova;
use App\Scopes\TeamScope;
use Davidpiesse\NovaToggle\Toggle;
use GeneaLabs\NovaFileUploadField\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Spatie\NovaTranslatable\Translatable;
use NovaErrorField\Errors ;

class UnitGeneralFeature extends Resource
{
    public static $parent = null;
    public static $group = 'Units';
    const DEFAULT_INDEX_ORDER = 'order';

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\UnitGeneralFeature';

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
        return __('General Features');
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return  string
     */
    public static function singularLabel()
    {
        return __('General Features');
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
//            FileUpload::make(__('Icon'), 'unit_feature_icon_id')
//                ->disk('local')
//                ->prunable(),
            Translatable::make([
                Text::make(__('Name'), 'name')->sortable()->rules('required'),
            ]),

            Toggle::make('Status', 'status')
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


    public static function indexQuery(NovaRequest $request, $query)
    {
        $query->when(empty($request->get('orderBy')), function (Builder $q) {
            $q->getQuery()->orders = [];

            return $q->orderBy(static::DEFAULT_INDEX_ORDER);
        });
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
