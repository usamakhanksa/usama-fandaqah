<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Resource;

class User extends Resource
{
    public static $model = '\App\Models\User';
    public static $title = 'name';
    public static $search = ['id', 'name', 'email'];

    public function fields(Request $request): array
    {
        return [
            ID::make()->sortable(),
            Text::make('Name'),
            Text::make('Email'),
        ];
    }
}
