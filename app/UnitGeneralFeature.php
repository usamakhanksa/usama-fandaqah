<?php

namespace App;

use App\Traits\HasTeam;
use App\UnitCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;
use Watson\Rememberable\Rememberable;
use App\Scopes\TeamScope;

class UnitGeneralFeature extends Model
{
    use Rememberable;
    protected $table = 'unit_general_feature';
    use LogsActivity;
    use HasTranslations;
    use HasTeam;


    public $translatable = ['name'];

    public function unit_feature_icon()
    {
        return $this->belongsTo('App\UnitFeatureIcons');
    }

    public function unit_categories()
    {
        return UnitCategory::all()->filter(function($category) {
            return in_array($this->id, $category->general_features_array) ? $category : null;
        });               
    }

    protected $attributes = [
        'order' => 0,
    ];

    protected $casts = [
        'name'  =>  'json'
    ];

    protected static function boot()
    {
        parent::boot();
        self::addGlobalScope(new TeamScope()); 
    }
}
