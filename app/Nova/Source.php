<?php

namespace App\Nova;

use Davidpiesse\NovaToggle\Toggle;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Spatie\NovaTranslatable\Translatable;
use NovaErrorField\Errors ;
use App\Scopes\TeamScope;

class Source extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Source';

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
        'name',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            Errors::make(),
            // ID::make()->sortable(),
            Translatable::make([
                Text::make(__('Source Name'), 'name')->sortable()->rules('required'),
            ]),

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
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }

    public function authorizedToUpdate(Request $request)
    {
        return ($this->getTranslation('name', 'en') != 'Recepion');
    }

    public function authorizedToDelete(Request $request)
    {
        return ($this->getTranslation('name', 'en') != 'Recepion');
    }


    public static function softDeletes()
    {
        return false ;
    }

    public static function searchable()
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

    public static function newModel()
    {
        $model = static::$model;
        $var = new $model;
        $var->team_id = auth()->user()->current_team_id;
        return $var;
    }

}
