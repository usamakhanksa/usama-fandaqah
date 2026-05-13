<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceQoyod extends Model
{
    protected $fillable = ['team_id', 'service_id', 'qoyod_account', 'qoyod_product', 'is_active'];

    public function service() { return $this->belongsTo(Service::class); }
    public function team()    { return $this->belongsTo(Team::class); }
}
