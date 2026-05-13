<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Scopes\TeamScope;

class Highlight extends Model
{
    use SoftDeletes;

    protected $fillable = ['team_id', 'name', 'color', 'status', 'order'];
    protected $casts = ['name' => 'array'];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new TeamScope());
    }

    public function team() { return $this->belongsTo(Team::class); }
}
