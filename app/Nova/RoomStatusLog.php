<?php

namespace App\Nova;

use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Resource;
use Illuminate\Http\Request;

class RoomStatusLog extends Resource
{
    public static $model = '\App\Models\RoomStatusLog';
    public static $title = 'id';
    public static $search = ['id', 'from_status', 'to_status', 'change_reason'];

    public function fields(Request $request): array
    {
        return [
            ID::make()->sortable(),
            BelongsTo::make(__('Unit'), 'unit', 'App\\Nova\\Unit')->display('unit_number'),
            BelongsTo::make(__('Changed By'), 'user', 'App\\Nova\\User')->display('name'),
            Text::make(__('From Status'), 'from_status'),
            Text::make(__('To Status'), 'to_status'),
            Text::make(__('Reason'), 'change_reason')->hideFromIndex(),
            DateTime::make(__('Changed At'), 'changed_at')->sortable(),
        ];
    }
}
