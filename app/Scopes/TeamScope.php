<?php

namespace App\Scopes;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Unit;
class TeamScope implements Scope {
    public function apply(Builder $builder, Model $model) {
        if (\Auth::check()) {
            $builder->where('team_id', auth()->user()->current_team_id);
        }
    }
}
