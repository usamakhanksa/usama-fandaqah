<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Resource;

class Unit extends Resource
{
    public static $model = '\App\Models\Unit';
    public static $title = 'name';
    public static $search = ['id', 'name', 'number'];

    public function fields(Request $request): array
    {
        return [
            ID::make()->sortable(),
            Text::make('Name'),
            Text::make('Number'),
        ];
    }
}
