<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\TeamScope;

class QuickPayment extends Model
{
    protected $appends = [
        'payment_status_label'
    ];

    protected static function boot()
    {
        parent::boot();
        //static::addGlobalScope(new TeamScope());
    }

    public function getPaymentStatusLabelAttribute(){
        return $this->payment_status ? 'Success' : 'Failed';
    }
}
