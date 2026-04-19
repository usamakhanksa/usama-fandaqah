<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Scopes\TeamScope;

class CompanyNote extends Model
{
    use SoftDeletes;

    protected static function boot()
    {
        parent::boot();
        //static::addGlobalScope(new TeamScope());
    }

    protected $guarded = [];

    protected $with = ['creator'];

    public function creator()
    {
        return $this->belongsTo(User::class , 'created_by');
    }
}
