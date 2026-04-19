<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
	/**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'end_date',
    ];

    /**
     * @return hasMany
     */
    public function logs()
    {
        return $this->hasMany(PromoCodeLog::class);
    }


    /**
     * @return hasMany
     */
    public function isPast()
    {
        return $this->end_date < Carbon::today();
    }

    /**
     * Get the user's first name.
     *
     * @param  string  $value
     * @return string
     */
    public function getIsValidAttribute($value)
    {
        return $this->is_active && !( isset($this->end_date) && $this->isPast() )&& ($this->logs->count() < $this->usage_limit);
    }
}
