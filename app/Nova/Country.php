<?php

namespace App\Nova;

use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class Country extends Resource
{
    public static $model = \App\Models\Country::class;
    public static $title = 'name';

    public function fields(NovaRequest $request): array
    {
        return [ID::make()->sortable(), Text::make('Name')->sortable(), Text::make('ISO2', 'iso2'), Text::make('Phone Code', 'phone_code')];
    }
}
