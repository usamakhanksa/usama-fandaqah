<?php

namespace App\Nova;

use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Http\Requests\NovaRequest;

class ActivityLog extends Resource {
    public static $model = \App\Models\ActivityLog::class;
    public static $title = 'id';
    public static $search = ['action', 'description', 'module'];
    public static $group = 'Settings';

    public function fields(NovaRequest $request) {
        return [
            ID::make()->sortable(),
            BelongsTo::make('User', 'user', User::class),
            Text::make('Module')->sortable(),
            Text::make('Action')->sortable(),
            Text::make('Description'),
            Text::make('IP Address', 'ip_address'),
            DateTime::make('Created At')->sortable(),
        ];
    }
    
    public static function authorizedToCreate(NovaRequest $request) { return false; }
    public function authorizedToDelete(NovaRequest $request) { return false; }
    public function authorizedToUpdate(NovaRequest $request) { return false; }
}
