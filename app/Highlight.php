<?php

namespace App;

use App\Traits\HasTeam;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;
use Watson\Rememberable\Rememberable;
use App\Scopes\TeamScope;

class Highlight extends Model
{
    use Rememberable;
    use HasTranslations, HasTeam, LogsActivity, SoftDeletes;

    public $translatable = ['name'];

    const palette = ['#2196F3', '#3F51B5', '#673AB7', '#9C27B0', '#E91E63', '#F44336', '#CDDC39', '#8BC34A', '#4CAF50', '#009688', '#00BCD4', '#03A9F4', '#607D8B', '#795548', '#FF5722', '#FF9800', '#FFC107', '#FFEB3B'];

    protected $attributes = [
        'order' => 0,
        'status' => 1,
    ];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new TeamScope());
    }
}
