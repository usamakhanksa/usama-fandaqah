<?php

namespace App;

use App\Traits\HasTeam;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Watson\Rememberable\Rememberable;

class Integration extends Model
{
    use Rememberable, HasTeam, SoftDeletes;

    /**
     * Get the SettingValues by its key.
     * @param string $key
     * @return Collection
     */
    public static function findByKey(string $key): Collection
    {
        if(\Auth::check())
        return self::query()->where(['key'=> $key,'team_id'=>\Auth::user()->current_team_id])->get();

        return null;
    }

    /**
     * Get the SettingValues by its key.
     * @param string $key
     * @return Collection
     */
    public static function findByKeyAndTeamId(string $key,$team_id): Collection
    {
        return self::query()->where(['key'=> $key,'team_id'=>$team_id])->get();
    }

    /**
     * Get the SettingValues by its key.
     * @param string $key
     * @return Collection
     */
    public function getValuesArrayAttribute()
    {
        return json_decode($this->values, true);
    }
}
