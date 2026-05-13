<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\Select;

class Bank extends Resource
{
    public static $model = \App\Models\Bank::class;
    public static $title = 'name';
    public static $search = ['id', 'name', 'name_ar', 'account_number', 'iban'];

    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),
            Text::make('Name')->rules('required', 'max:255'),
            Text::make('Name Arabic', 'name_ar')->hideFromIndex(),
            Text::make('Bank Code/SWIFT', 'code'),
            Text::make('Branch'),
            Text::make('Account Number'),
            Text::make('IBAN')->hideFromIndex(),
            Text::make('Account Name')->hideFromIndex(),
            Select::make('Currency')->options([
                'SAR' => 'SAR',
                'USD' => 'USD',
                'EUR' => 'EUR',
            ])->default('SAR'),
            Boolean::make('Is Active', 'is_active'),
            Textarea::make('Notes'),
        ];
    }
}
