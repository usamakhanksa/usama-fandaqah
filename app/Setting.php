<?php

namespace App;

use App\Traits\HasTeam;
use Illuminate\Database\Eloquent\Model;
use Watson\Rememberable\Rememberable;
use App\Scopes\TeamScope;

class Setting extends Model
{
    use Rememberable;
    use HasTeam;

    protected $guarded = [];

    protected $casts = [
        'alert_new_reservation' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new TeamScope());
    } 

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
