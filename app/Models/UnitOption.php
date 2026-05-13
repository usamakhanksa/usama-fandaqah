<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\TeamScope;

class UnitOption extends Model
{
    protected $table = 'unit_options';

    protected $fillable = ['team_id', 'name', 'price', 'description', 'active'];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new TeamScope());
    }

    public function team() { return $this->belongsTo(Team::class); }
}
