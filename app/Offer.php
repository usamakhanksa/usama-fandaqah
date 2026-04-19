<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Scopes\TeamScope;

class Offer extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'categories' => 'array',
        'categories_ids' => 'array',
        'days' => 'array'
    ];

    protected $appends = ['offer_dates_status'];


    protected static function boot()
    {
        parent::boot();
        static::creating(function ($query) {
            $query->team_id = auth()->user()->current_team_id;
        });
        //static::addGlobalScope(new TeamScope());
    }

    /**
     * Determine the status of dates according to the offer
     * @return string
     */
    public function getOfferDatesStatusAttribute(){

        if(Carbon::parse($this->end_date)->lt(Carbon::now())){
            return 'out-dated';
        }

        if(Carbon::parse($this->start_date)->gt(Carbon::now())){
            return 'incoming';
        }

        $startDate = Carbon::createFromFormat('Y-m-d' , $this->start_date);
        $endDate   = Carbon::createFromFormat('Y-m-d' , $this->end_date);

        return Carbon::now()->between($startDate,$endDate) ? 'between' : 'not-between' ;
    }

    public function scopeWhereDoesntHaveIntersection($query, $start , $end)
    {
        return $query->whereRaw('? between start_date and end_date', [$start])
            ->orWhereRaw('? between start_date and end_date', [$end]);
    }

    public function scopeWhereIntersectsStartDate($query, $start_date)
    {

        return  $query->whereNotNull('start_date')
            ->where('start_date', '>', $start_date)
            ->orWhere('end_date', '>', $start_date);
    }

    public function scopeWhereIntersectsEndDate($query, $end_date)
    {

        return  $query->whereNotNull('end_date')
            ->where('end_date', '<', $end_date)
            ->orWhere('start_date', '<', $end_date);
    }

    public function scopeWhereCategoryId($query , $id){
        return $query->whereJsonContains('categories_ids', $id);
    }


}
