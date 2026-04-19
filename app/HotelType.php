<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class HotelType extends Model
{
    use HasTranslations;

    public $translatable = ['title'];

    protected $appends = ['count_hotels'];

    public function hotels()
    {
        return $this->hasMany(Hotel::class, 'type_id', 'id');
    }

    public function getCountHotelsAttribute()
    {
        return $this->hotels()->count();
    }
}
