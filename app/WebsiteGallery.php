<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Watson\Rememberable\Rememberable;

class WebsiteGallery extends Model
{
    use Rememberable;

    protected $fillable = [
        'path' , 'type' , 'object'
    ];

    protected $casts = [
        'object'    =>  'array'
    ];
}
