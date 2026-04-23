<?php

namespace App\Nova;

use App\Models\EodProcess as EodProcessModel;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Http\Requests\NovaRequest;

class EodProcess extends Resource {
    public static $model = EodProcessModel::class;
    public static $title = 'audit_date';
    public static $search = ['audit_date', 'run_by'];

    public function fields(NovaRequest $request): array {
        return [
            ID::make()->sortable(),
            Date::make('Audit Date')->sortable()->rules('required'),
            Currency::make('Total Revenue')->currency('SAR')->sortable()->readonly(),
            Currency::make('Total Payments')->currency('SAR')->sortable()->readonly(),
            Currency::make('Total Comps')->currency('SAR')->sortable()->readonly(),
            Badge::make('Status')->map([
                'pending' => 'warning',
                'completed' => 'success',
                'failed' => 'danger',
            ])->sortable(),
            Text::make('Run By')->sortable()->readonly(),
            DateTime::make('Completed At')->sortable()->readonly(),
        ];
    }
}
