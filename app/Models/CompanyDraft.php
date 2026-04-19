<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyDraft extends Model
{
    protected $guarded = [];

    protected $casts = [
        'payload' => 'array',
    ];
}
