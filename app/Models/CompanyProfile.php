<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyProfile extends Model
{
    protected $guarded = [];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function contactPeople()
    {
        return $this->hasMany(ContactPerson::class);
    }

    public function guests()
    {
        return $this->hasMany(Guest::class);
    }

    public function media()
    {
        return $this->morphMany(UploadedMedia::class, 'owner');
    }
}
