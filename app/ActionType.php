<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Scopes\TeamScope;

class ActionType extends Model
{
    use SoftDeletes;

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new TeamScope());

        static::deleting(function (ActionType $actionType) {
            $actions = $actionType->unitMaintenance()->whereNull('completed_by')->get();
            if (count($actions) > 0) {
                // Prevent deletion
                return false;
            }
        });
    }
    public function get($type, $key_en) {
        $team_id =  auth()->user()->current_team_id;
        $types = self::where('team_id', $team_id)
                ->where(['action' => $type])
                ->all();
        return $types;
    }

    public function unitMaintenance()
    {
        return $this->hasMany(UnitMaintenance::class, 'action_id', 'id');
    }
}
