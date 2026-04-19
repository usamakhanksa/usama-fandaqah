<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactPerson extends Model
{
    protected $guarded = [];

    public function companyProfile()
    {
        return $this->belongsTo(CompanyProfile::class);
    }
}
