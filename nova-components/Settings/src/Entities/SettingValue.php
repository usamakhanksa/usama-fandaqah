<?php

namespace Surelab\Settings\Entities;

use App\Reservation;
use App\Transaction;
use App\Unit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Vinkla\Hashids\Facades\Hashids;

/**
 * Class SettingValue
 * @package Surelab\Settings\Entities
 */
final class SettingValue extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'settings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'key', 'value','team_id'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($query) {
            $query->team_id = auth()->user()->current_team_id;
        });

        static::addGlobalScope('team_id', function (Builder $builder) {
            if(auth()->check()){
                $team_id = auth()->user()->current_team_id ;
            }else if (request()->route('id') && !request()->routeIs('corner-search-without-mw')) {
                $swap_id  = Hashids::decode(request()->route('id'))[0];
                $transaction = Transaction::find($swap_id);
                if(!is_null($transaction) && !request()->routeIs('corneer-api.getUnit')){
                    // it's transaction
                    if($transaction->payable_type == 'App\Reservation'){
                        $team_id  = Reservation::withOutGlobalScope('team_id')->find($transaction->payable_id)->team_id ;
                    }else{
                        $team_id  = $transaction->payable_id ;
                    }
                }elseif (!request()->routeIs('corneer-api.getUnit')){
                    // it's reservation id
                   $team_id  = Reservation::withOutGlobalScope('team_id')->find($swap_id)->team_id ;
                }elseif (request()->routeIs('corneer-api.getUnit') ){
                    $team_id = \View::getShared()['currentTeamId'];
                    // $team_id  =  Unit::withOutGlobalScope('team_id')->find($swap_id)->team_id;
                }
                // This was the old implementation
                //                $reservation_id  = Hashids::decode(request()->route('id'))[0];
                //                $team_id  = Reservation::find($reservation_id)->team_id ;
            }

            if (!empty($team_id))
                $builder->where('team_id', '=', $team_id);
        });
    }

    /**
     * Get the SettingValues by its key.
     * @param string $key
     * @return Collection
     */
    public static function findByKey(string $key): Collection
    {
        if (empty($key)) {
            return collect();
        }

        return self::query()->where('key', $key)->get();
        // dd(!is_null(auth()->user()));

        // if(auth()->user()){
        //     $current_team_id = auth()->user()->current_team_id;
        // }else{
        //     $current_team_id = view()->getShared()['current_team_id'];
        // }

        // $results = cache()->rememberForever('settings_' . $key . '_' . $current_team_id , function() use ($key) {
        //     return serialize(self::query()->where('key', $key)->get());
        // });
        // $results = @unserialize($results);

        // return is_object($results) ? $results : collect();
    }
}
