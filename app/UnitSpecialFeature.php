<?php

namespace App;

use App\Traits\HasTeam;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;
use Watson\Rememberable\Rememberable;
use App\Scopes\TeamScope;

class UnitSpecialFeature extends Model
{
    use Rememberable;
    use HasTranslations, HasTeam, LogsActivity;

    public $translatable = ['name'];
    protected $attributes = [
        'order' => 0,
    ];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new TeamScope());
    } 


    public function unit_categories()
    {
        return UnitCategory::all()->filter(function($category) {
            return in_array($this->id, $category->special_features_array) ? $category : null;
        });               
    }
}
