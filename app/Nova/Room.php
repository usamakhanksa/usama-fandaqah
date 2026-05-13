<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Resource;

class Room extends Resource
{
    public static $model = '\App\Models\Room';
    public static $title = 'number';
    public static $search = ['id', 'number', 'name'];

    public function fields(Request $request): array
    {
        return [
            ID::make()->sortable(),
            Text::make('Name'),
            Text::make('Number'),
        ];
    }
}
