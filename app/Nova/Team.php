<?php

namespace App\Nova;

use Carbon\Carbon;
use App\Traits\HasTeam;
use Laravel\Spark\Spark;
use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Avatar;
use Davidpiesse\NovaToggle\Toggle;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Http\Requests\NovaRequest;
use Ebess\AdvancedNovaMediaLibrary\Fields\Images;
use Laravel\Spark\Contracts\Repositories\TeamRepository;

class Team extends Resource
{

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Team';

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
     * Get the displayable label of the resource.
     *
     * @return  string
     */
    public static function label()
    {
        return __('Teams');
    }



    /**
     * Get the displayable singular label of the resource.
     *
     * @return  string
     */
    public static function singularLabel()
    {
        return __('Team');
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            Avatar::make(__('Logo'), 'photo_url', 's3')
                ->path('logos'),
            Avatar::make(__('Group Logo'), 'group_photo_url', 's3')
                ->path('logos'),
            Avatar::make(__('General Background'), 'general_background_url', 's3')
                ->path('logos'),
            Text::make(__('Facility Name'), 'name')->withMeta(['extraAttributes' => [
                'readonly' => true
            ]]),
            Text::make(__('Plan'), 'current_billing_plan')->displayUsing(function () {
                $subscription_label = '';
                if ($this->last_subscription && $this->last_subscription->stripe_plan == 'team-basic' && $this->last_subscription->shomos) {
                    $subscription_label =  __('Advanced');
                }

                if ($this->last_subscription && $this->last_subscription->stripe_plan == 'team-basic' && !$this->last_subscription->shomos) {
                    $subscription_label =  __('Basic');
                }

                if ($this->last_subscription && $this->last_subscription->stripe_plan == 'monthly-yearly') {
                    $subscription_label =  __('Monthly Yearly');
                }


                if ($this->last_subscription && ($this->last_subscription->stripe_plan == 'trial' || $this->last_subscription->stripe_plan == 'team-free') && !$this->last_subscription->shomos) {
                    $subscription_label =  __('Trial');
                }

                return $subscription_label;
                // return isset(\App\Team::$plans[$this->current_billing_plan]) ? __(\App\Team::$plans[$this->current_billing_plan]) : __(\App\Team::$plans['team-free']);
            })->exceptOnForms(),
            $this->current_billing_plan == 'trial' ?  Text::make(__('Ends At'), 'trial_ends_at')->displayUsing(function () {
                return Carbon::parse($this->trial_ends_at)->format('Y-m-d');
            })->hideWhenCreating()->hideWhenUpdating() : Text::make(__('Ends At'), 'ends_at')->displayUsing(function () {
                return Carbon::parse($this->ends_at)->format('Y-m-d');
            })->hideWhenCreating()->hideWhenUpdating()

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


    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->where('owner_id', auth()->user()->getAuthIdentifier())->get();
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
        $var->owner_id = auth()->user()->id;
        return $var;
    }

    public static function authorizedToCreate(Request $request)
    {
        return false;
    }
}
