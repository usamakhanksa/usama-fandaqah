<?php

namespace App;

use App\Traits\HasTeam;
use Watson\Rememberable\Rememberable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServicesCategory extends Model
{
    use Rememberable, HasTranslations, HasTeam, LogsActivity, SoftDeletes;

    public $translatable = ['name'];
    protected $table = 'service_categories';
    protected $attributes = [
        'order' => 0,
        'status' => 1,
        'show_in_reservation' => 1,
        'show_in_pos' => 1
    ];

    public static function boot()
    {
        if (auth()->user()) {
            $team_id = (auth()->user()->current_team_id) ? auth()->user()->current_team_id : 0;
            static::addGlobalScope('team_id', function (Builder $builder) use ($team_id) {
                $builder->where('team_id', '=', $team_id);
            });
            static::created(function ($category) use ($team_id) {

                if (!count(json_decode($category->users))) {
                    $users = auth()->user()->current_team->users;
                    $admin_users_ids = [];
                    if (count($users)) {
                        foreach ($users as $user) {
                            if ($user->isAdmin()) {
                                $admin_users_ids[] = $user->id;
                            }
                        }
                    }

                    $category->users = json_encode($admin_users_ids);
                    $category->save();
                }
            });
        }



        parent::boot();
    }
    public function services(): HasMany
    {
        return $this->hasMany('App\Service', 'services_category_id')->orderBy('order', 'asc');
    }

    public function servicesForReservation(): HasMany
    {
        return $this->hasMany('App\Service', 'services_category_id');
    }
}
