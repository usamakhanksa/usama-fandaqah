<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class City extends Model
{
    use HasTranslations;

    protected $guarded = [];

    public $translatable = ['title'];

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id', 'code');
    }

    public function team(){
        return $this->hasMany(Team::class,'city_id');
    }

}
