<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartDraft extends Model
{
    protected $guarded = [];

    protected $casts = [
        'items' => 'array',
    ];
}
