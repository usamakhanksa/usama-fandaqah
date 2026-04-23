<?php

namespace App\Nova;

use App\Models\ArAccount as ArAccountModel;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Http\Requests\NovaRequest;

class ArAccount extends Resource {
    public static $model = ArAccountModel::class;
    public static $title = 'company_name';
    public static $search = ['company_name', 'contact_person'];

    public function fields(NovaRequest $request): array {
        return [
            ID::make()->sortable(),
            Text::make('Company Name')->sortable()->rules('required'),
            Text::make('Contact Person')->sortable(),
            Text::make('Email')->rules('email', 'required'),
            Text::make('Phone')->rules('required'),
            Currency::make('Credit Limit')->currency('SAR')->sortable(),
            Currency::make('Current Balance')->currency('SAR')->sortable()->exceptOnForms(),
            Select::make('Status')->options([
                'active' => 'Active', 
                'suspended' => 'Suspended', 
                'closed' => 'Closed'
            ])->displayUsingLabels(),
            
            HasMany::make('Transactions', 'transactions', FinancialTransaction::class),
        ];
    }
}
