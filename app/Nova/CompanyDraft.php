<?php

namespace App\Nova;

use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Http\Requests\NovaRequest;

class CompanyDraft extends Resource
{
    public static $model = \App\Models\CompanyDraft::class;
    public static $title = 'id';

    public function fields(NovaRequest $request): array
    {
        return [ID::make()->sortable(), Code::make('Payload')->json()];
    }
}
