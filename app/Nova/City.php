<?php

namespace App\Nova;

use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class City extends Resource
{
    public static $model = \App\Models\City::class;
    public static $title = 'name';

    public function fields(NovaRequest $request): array
    {
        return [ID::make()->sortable(), BelongsTo::make('Country'), Text::make('Name')->sortable()];
    }
}
