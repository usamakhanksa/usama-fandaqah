<?php

namespace App\Nova;

use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Http\Requests\NovaRequest;

class Rating extends Resource
{
    public static $model = \App\Rating::class;

    public static $title = 'id';

    public static $group = 'Booking Management';

    public static $search = [
        'id', 'rating'
    ];

    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),

            BelongsTo::make('Reservation')->searchable(),
            BelongsTo::make('Guest')->searchable(),
            
            Number::make('Rating')->min(1)->max(5),
            Textarea::make('Feedback'),
            Boolean::make('Published'),
            
            DateTime::make('Created At')->onlyOnDetail(),
            DateTime::make('Updated At')->onlyOnDetail(),
        ];
    }

    public function cards(NovaRequest $request)
    {
        return [];
    }

    public function filters(NovaRequest $request)
    {
        return [
            // Assuming you have a RatingFilter in the correct namespace
            // new Filters\RatingFilter,
        ];
    }

    public function lenses(NovaRequest $request)
    {
        return [];
    }

    public function actions(NovaRequest $request)
    {
        return [];
    }

    public static function label()
    {
        return __('Customer Ratings');
    }

    public static function singularLabel()
    {
        return __('Customer Rating');
    }
}
