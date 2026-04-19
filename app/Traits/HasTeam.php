<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasTeam
{
    protected static function boot()
    {
        if (\Auth::check()) {
            $team_id = (auth()->user()->current_team_id) ? auth()->user()->current_team_id : 0;
            $team_id = request()->get('current_team_id', $team_id);
            // auto-sets values on creation
            static::creating(function ($query) use($team_id ){
                if (empty($query->team_id))
                    $query->team_id = $team_id;
            });

            static::addGlobalScope('team_id', function (Builder $builder) use($team_id ){
                $builder->where('team_id', '=', $team_id);
            });
        }
        parent::boot();
    }
}
