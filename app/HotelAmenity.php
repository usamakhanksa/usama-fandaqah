<?php

namespace App;

use App\Traits\HasTeam;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;

class HotelAmenity extends Model implements HasMedia
{
    use HasTeam;
    use LogsActivity;

    use HasMediaTrait {
        HasMediaTrait::addMedia as parentAddMedia;
    }

    protected $guarded = [];
    
    public function registerMediaCollections()
    {
        $this->addMediaCollection('main')->singleFile();
        $this->addMediaCollection('images');
    }
}
