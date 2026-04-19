<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\TeamScope;

class FormIntegration extends Model
{
    // protected static function boot()
    // {
    //     parent::boot();
    //     static::addGlobalScope(new TeamScope());
    // }
    protected $casts = [
        'data'  =>  'array',
        'response'  =>  'array'
    ];

    protected $fillable = ['team_id' , 'integration_name' , 'status' , 'data' , 'response'];

    protected $guraded = [];


    public function team()
    {
        $this->belongsTo(Team::class);
    }
}
