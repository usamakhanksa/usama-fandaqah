<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\TeamScope;

class NotificationControl extends Model
{

    protected $guarded = [];


    protected $casts = [
        'value' => 'json'
    ];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new TeamScope());
    }

}
