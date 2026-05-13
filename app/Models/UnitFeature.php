<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\TeamScope;

class UnitFeature extends Model
{
    protected $table = 'unit_features';

    protected $fillable = ['team_id', 'name', 'icon', 'description', 'active'];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new TeamScope());
    }

    public function team() { return $this->belongsTo(Team::class); }
}
