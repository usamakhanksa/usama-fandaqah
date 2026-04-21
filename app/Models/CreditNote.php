<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditNote extends Model
{
    protected $guarded = [];

    protected $casts = [
        'date' => 'date',
        'creation_date' => 'date',
        'collection_date' => 'date',
        'payload' => 'array',
    ];

    public function employee()
    {
        return $this->belongsTo(\App\User::class, 'employee_id');
    }
}
