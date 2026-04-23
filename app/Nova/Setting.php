<?php

namespace App\Nova;

use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Http\Requests\NovaRequest;

class Setting extends Resource {
    public static $model = \App\Models\Setting::class;
    public static $title = 'key';
    public static $search = ['key', 'group'];
    public static $group = 'Settings';

    public function fields(NovaRequest $request) {
        return [
            ID::make()->sortable(),
            Text::make('Group')->sortable(),
            Text::make('Key')->sortable(),
            KeyValue::make('Payload'),
        ];
    }
}
