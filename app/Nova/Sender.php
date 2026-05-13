<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\Select;

class Sender extends Resource
{
    public static $model = \App\Models\Sender::class;
    public static $title = 'name';
    public static $search = ['id', 'name', 'name_ar', 'email', 'phone'];

    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),
            Text::make('Name')->rules('required', 'max:255'),
            Text::make('Name Arabic', 'name_ar')->hideFromIndex(),
            Select::make('Type')->options([
                'individual' => 'Individual',
                'company' => 'Company',
                'government' => 'Government',
            ])->rules('required'),
            Text::make('ID Number', 'id_number'),
            Text::make('Phone'),
            Text::make('Email'),
            BelongsTo::make('Bank')->nullable(),
            Boolean::make('Is Active', 'is_active'),
            Textarea::make('Address')->hideFromIndex(),
            Textarea::make('Notes')->hideFromIndex(),
        ];
    }
}
