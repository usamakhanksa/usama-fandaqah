<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Watson\Rememberable\Rememberable;

class IntegrationSettings extends Model
{
    use Rememberable;
    use HasTranslations;

    public $translatable = ['name','description'];


    public function integration()
    {
        return $this->hasOne('App\Integration','key','key');
    }
}
