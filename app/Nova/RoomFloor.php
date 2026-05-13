<?php

namespace App\Nova;

use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\HasMany;
use Illuminate\Http\Request;

class RoomFloor extends Resource
{
    public static $model = '\App\Models\RoomFloor';
    public static $title = 'name';
    public static $search = ['id', 'name', 'level'];

    public function fields(Request $request): array
    {
        return [
            ID::make()->sortable(),
            Text::make(__('Name'), 'name')->sortable()->rules('required', 'max:255'),
            Number::make(__('Level'), 'level')->sortable()->rules('required', 'integer'),
            HasMany::make(__('Rooms'), 'rooms', 'App\\Nova\\Room'),
        ];
    }
}
