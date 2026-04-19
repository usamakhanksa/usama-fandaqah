<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Scopes\TeamScope;

class WebsitePage extends Model
{
    use SoftDeletes;

    protected $casts = [
        'title'  =>  'array',
        'content' => 'array'
    ];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new TeamScope());
    } 

}
