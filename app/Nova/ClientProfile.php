<?php

namespace App\Nova;

use App\Models\ClientProfile as ClientProfileModel;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\HasOne;
use Laravel\Nova\Http\Requests\NovaRequest;

class ClientProfile extends Resource {
    public static $model = ClientProfileModel::class;
    public static $title = 'first_name';
    public static $search = ['id', 'first_name', 'last_name', 'email', 'phone'];

    public function fields(NovaRequest $request) {
        return [
            ID::make()->sortable(),
            Text::make('First Name')->rules('required', 'max:255'),
            Text::make('Last Name')->rules('required', 'max:255'),
            Text::make('Email')->rules('required', 'email', 'max:255')->creationRules('unique:client_profiles,email'),
            Text::make('Phone')->rules('required', 'max:20'),
            Text::make('National ID', 'national_id')->rules('nullable', 'max:255'),
            Select::make('Type')->options([
                'tenant' => 'Tenant',
                'buyer' => 'Buyer',
                'investor' => 'Investor',
            ])->rules('required'),
            Text::make('City')->rules('nullable', 'max:255'),
            Text::make('Address')->rules('nullable', 'max:255'),
            
            HasOne::make('Membership', 'membership', ClientMembership::class),
            HasMany::make('Activities', 'activities', ClientActivity::class),
            HasMany::make('Sales', 'sales', ClientSale::class),
        ];
    }
}
