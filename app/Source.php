<?php

namespace App;

use App\Traits\HasTeam;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;
use Watson\Rememberable\Rememberable;
use App\Scopes\TeamScope;

class Source extends Model
{
    use Rememberable;
    use HasTranslations, HasTeam, LogsActivity, SoftDeletes;

    protected $guarded = [];
    public $translatable = ['name'];

    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;
    protected static $logAttributes = [
                    '*'
                ];


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
