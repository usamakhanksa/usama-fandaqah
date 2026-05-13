<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Filterable;

class CompanyProfile extends Model
{
    use SoftDeletes, Filterable;

    protected $guarded = [];
    protected $searchable = ['company_name', 'email', 'mobile_number', 'tax_number'];

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

    public function companyGroup()
    {
        return $this->belongsTo(CompanyGroup::class);
    }

    public function media()
    {
        return $this->morphMany(UploadedMedia::class, 'owner');
    }

    public function promissories()
    {
        return $this->hasMany(Promissory::class, 'company_id');
    }
}
