<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Watson\Rememberable\Rememberable;

class Task extends Model
{
    use Rememberable;

    protected $casts = [
        'meta'  =>  'array'
    ];

    public function taskable()
    {
        return $this->morphTo();
    }
}
