<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\TeamScope;

class OnlinePaymentServiceInvoice extends Model
{
    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();
        //static::addGlobalScope(new TeamScope());
    }


    public function scopeByTeam($query, $value)
    {
        return $query->where('team_id', '=', $value) ;
    }
    
    public function scopeByDateFrom($query, $value)
    {
        return $query->where('created_at', '>=', $value) ;
    }
    
    public function scopeByDateTo($query, $value)
    {
        return $query->where('created_at', '<=', $value) ;
    }
}
