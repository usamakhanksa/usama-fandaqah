<?php

namespace App\Nova;

use App\User;
use NovaErrorField\Errors;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\HasMany;

use Davidpiesse\NovaToggle\Toggle;
use Spatie\NovaTranslatable\Translatable;
use OptimistDigital\MultiselectField\Multiselect;
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Scopes\TeamScope;
class ServicesCategory extends Resource
{
    public static $displayInNavigation = false;

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\ServicesCategory';

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
        return __('Services Category');
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return  string
     */
    public static function singularLabel()
    {
        return __('Services Category');
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param Request $request
     * @return array
     */
    public function fields(Request $request)
    {
        $users = auth()->user()->current_team->users()->pluck('name', 'id');
        // $users = User::where('current_team_id', auth()->user()->current_team_id)->get()->pluck('name', 'id');

        return [

            Errors::make(),

            Translatable::make([
                Text::make(__('Category Name'), 'name')->sortable()->rules('required'),
            ]),

            Toggle::make(__('Status'), 'status')
                ->trueValue(1)
                ->falseValue(0)
                ->trueLabel(__('Active'))
                ->falseLabel(__('Inactive'))
                ->falseColor('#FF0000'),

            Toggle::make(__('Show in reservation'), 'show_in_reservation')
                ->trueValue(1)
                ->falseValue(0)
                ->trueLabel(__('Active'))
                ->falseLabel(__('Inactive'))
                ->falseColor('#FF0000'),

            Toggle::make(__('Show in pos'), 'show_in_pos')
                ->trueValue(1)
                ->falseValue(0)
                ->trueLabel(__('Active'))
                ->falseLabel(__('Inactive'))
                ->falseColor('#FF0000'),

            Number::make(__('Display Order'), 'order')
                ->rules('required'),

            HasMany::make(__('Services'), 'services', Service::class),
            Multiselect::make(__('Users'), 'users')
                ->options($users)
                ->placeholder(__('Select users who can access this service category'))
                ->hideFromIndex(),
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
        return $var;
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
