<?php
namespace App\Nova\Foundation;

use App\Models\Foundation\Unit as UnitModel;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class Unit extends \Laravel\Nova\Resource
{
    public static $model = UnitModel::class;
    public static $title = 'number';
    public static $search = ['id','name','number','code'];
    public function fields(NovaRequest $request): array
    {
        return [ID::make()->sortable(),BelongsTo::make('Category','category',UnitCategory::class)->rules('required'),Text::make('Name')->rules('required'),Text::make('Number')->rules('required'),Text::make('Code')->nullable(),Number::make('Floor','floor_no'),Number::make('Max Occupancy','max_occupancy')->rules('required'),Number::make('Current Rate SAR','current_rate_sar')->step(0.01),Select::make('Status')->options(['active'=>'Active','out_of_service'=>'Out of Service','dirty'=>'Dirty','maintenance'=>'Maintenance'])->displayUsingLabels()];
    }
}
