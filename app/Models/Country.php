<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $guarded = [];

    protected $casts = [
        'title' => 'array',
    ];

    public function cities()
    {
        return $this->hasMany(City::class);
    }
}
