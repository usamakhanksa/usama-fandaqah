<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UploadedMedia extends Model
{
    protected $guarded = [];

    public function owner()
    {
        return $this->morphTo();
    }
}
