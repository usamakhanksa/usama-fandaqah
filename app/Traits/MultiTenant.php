<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait MultiTenant
{
    protected static function bootMultiTenant()
    {
        if (Auth::check()) {
            $teamId = Auth::user()->current_team_id;

            static::creating(function ($model) use ($teamId) {
                if (empty($model->team_id)) {
                    $model->team_id = $teamId;
                }
            });

            static::addGlobalScope('team_id', function (Builder $builder) use ($teamId) {
                $builder->where('team_id', $teamId);
            });
        }
    }

    public function scopeForTeam($query, $teamId)
    {
        return $query->where('team_id', $teamId);
    }
}
