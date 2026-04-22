<?php
namespace App\Nova\Foundation;

use App\Models\Foundation\UnitCategory as UnitCategoryModel;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class UnitCategory extends \Laravel\Nova\Resource
{
    public static $model = UnitCategoryModel::class;
    public static $title = 'name_en';
    public static $search = ['id','name_en','name_ar','code'];
    public function fields(NovaRequest $request): array
    {
        return [ID::make()->sortable(),Text::make('Name EN','name_en')->rules('required'),Text::make('Name AR','name_ar')->rules('required'),Text::make('Code')->rules('required'),Number::make('Default Capacity','default_capacity')->rules('required'),Number::make('Base Rate SAR','base_rate_sar')->step(0.01)->rules('required'),Boolean::make('Active')];
    }
}
