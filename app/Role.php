<?php

namespace App;

use App\Traits\HasTeam;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Model;
use Pktharindu\NovaPermissions\Policies\Policy;
use Watson\Rememberable\Rememberable;

class Role extends \Pktharindu\NovaPermissions\Role
{
    use Rememberable;

    protected static function boot()
    {
        if (\Auth::check()) {
            // auto-sets values on creation
            static::creating(function ($query) {
                if(empty($query->team_id))
                $query->team_id = auth()->user()->current_team_id;
            });

            static::addGlobalScope('team_id', function (Builder $builder) {
                $builder->where('team_id', '=', auth()->user()->current_team_id);
            });
        }


        parent::boot();
    }

    public $niceNames =[];
}
