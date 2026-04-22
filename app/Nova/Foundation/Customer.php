<?php
namespace App\Nova\Foundation;

use App\Models\Foundation\Customer as CustomerModel;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class Customer extends \Laravel\Nova\Resource
{
    public static $model = CustomerModel::class;
    public static $title = 'full_name_en';
    public static $search = ['id','first_name_en','last_name_en','phone','document_number'];
    public function fields(NovaRequest $request): array
    {
        return [ID::make()->sortable(),Text::make('First Name EN','first_name_en')->rules('required'),Text::make('Last Name EN','last_name_en')->rules('required'),Text::make('First Name AR','first_name_ar')->nullable(),Text::make('Last Name AR','last_name_ar')->nullable(),Text::make('Phone')->rules('required'),Select::make('Document Type')->options(['saudi_id'=>'Saudi ID','iqama'=>'Iqama','passport'=>'Passport']),Text::make('Document Number')->rules('required'),Boolean::make('VIP','vip')];
    }
}
