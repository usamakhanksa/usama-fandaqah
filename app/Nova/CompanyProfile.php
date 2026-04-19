<?php

namespace App\Nova;

use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class CompanyProfile extends Resource
{
    public static $model = \App\Models\CompanyProfile::class;
    public static $title = 'company_name';
    public static $search = ['id', 'company_name', 'email'];

    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),
            Text::make('Company Name', 'company_name')->sortable()->rules('required'),
            Text::make('Email')->sortable(),
            Text::make('Mobile Number', 'mobile_number'),
        ];
    }
}
