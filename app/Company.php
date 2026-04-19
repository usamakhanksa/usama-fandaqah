<?php

namespace App;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Scopes\TeamScope;

class Company extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    // protected static function boot()
    // {
    //     parent::boot();
    //     static::addGlobalScope(new TeamScope());
    // }

    public function scopeByCompanyName($query , $value)
    {
        return $query->where('name' , 'LIKE' , "%$value%");
    }

    public function scopeByCompanyPhone($query , $value)
    {
        return $query->where('phone' , 'LIKE' , "%$value%");
    }
    public function scopeByCreator($query , $value)
    {
        return $query->where('user_id' , $value);
    }

    public function creator()
    {
        return $this->belongsTo(User::class , 'user_id');
    }

    public function scopeBySearch($query, $value)
    {
        return $query->where('name', 'like', '%' . $value . '%')
            ->orWhere('phone', 'like', '%' . $value . '%')
            ->orWhere('email', 'like', '%' . $value . '%');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class , 'company_id')
            ->with(['unit','customer'])
            // ->whereDoesntHave('group_reservation')
            ->where('status' , 'confirmed')
            ->whereNull('attachable_id')
            ->whereNull('deleted_at')
            ->whereNull('checked_out')
            ->orderByDesc('id');
    }



    public function company_reservations_history()
    {
        return $this->hasMany(Reservation::class , 'company_id')
            ->with(['unit','customer'])
            // ->whereDoesntHave('group_reservation')
            ->whereIn('status' , ['confirmed'])
            ->whereNull('attachable_id')
            ->whereNull('deleted_at')
            // ->whereNull('checked_out')
            ->orderByDesc('id');
    }

    public function companyGroupReservations()
    {
        return $this->hasMany(Reservation::class , 'company_id')
            ->with(['unit','customer'])
            ->where('company_id' , $this->id)
            // ->whereDoesntHave('group_reservation')
            ->whereIn('status' , ['confirmed','canceled'])
            // ->whereNull('attachable_id')
            ->whereNull('deleted_at')
            // ->whereNull('checked_out')
            ->orderByDesc('id');
    }

    public function group_reservations()
    {
       return $this->hasMany(GroupReservation::class)
       ->whereNull('deleted_at');
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function scopeByCompanyType($query , $value)
    {
        return $query->where('entity_type' , $value);
    }
}
