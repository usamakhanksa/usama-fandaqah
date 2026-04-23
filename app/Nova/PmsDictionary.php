<?php

namespace App\Nova;

use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Http\Requests\NovaRequest;

class PmsDictionary extends Resource {
    public static $model = \App\Models\PmsDictionary::class;
    public static $title = 'label';
    public static $search = ['label', 'group'];
    public static $group = 'Settings';

    public function fields(NovaRequest $request) {
        return [
            ID::make()->sortable(),
            Text::make('Group')->sortable(),
            Text::make('Label')->sortable(),
            Boolean::make('Is Active'),
            KeyValue::make('Meta'),
        ];
    }
}
