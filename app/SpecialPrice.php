<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Scopes\TeamScope;

class SpecialPrice extends Model
{
    use SoftDeletes;

    protected $with = ['unit_category'];

    protected $guarded = [];

    protected $casts = [
        'days_prices' => 'json'
    ];

    protected static function boot()
    {
        parent::boot();
        //static::addGlobalScope(new TeamScope());
        static::creating(function ($query) {
            $query->team_id = auth()->user()->current_team_id;
        });
    }

    public function unit_category()
    {
        return $this->belongsTo(UnitCategory::class);
    }


    public function scopeWhereIntersectsStartDate($query,$start_date){

        return  $query->whereNotNull('start_date')
            ->where('start_date' , '>' , $start_date)
            ->orWhere('end_date' , '>=' , $start_date);
    }

    public function scopeWhereIntersectsEndDate($query,$end_date){

        return  $query->whereNotNull('end_date')
                    ->where('end_date' , '<=' , $end_date)
                    ->orWhere('start_date' , '<' , $end_date);
    }

}
