<?php

namespace App;

use App\Traits\HasTeam;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;
use Watson\Rememberable\Rememberable;
use App\Scopes\TeamScope;

class Service extends Model
{
    use Rememberable;
    use SoftDeletes;
    use HasTranslations;
    use HasTeam;
    use LogsActivity;

    public $translatable = ['name'];
    protected $attributes = [
        'order' => 0,
        'status' => 1,
    ];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new TeamScope());
    } 

    public function serviceCategory(){
        return $this->belongsTo(ServicesCategory::class , 'services_category_id');
    }


}
