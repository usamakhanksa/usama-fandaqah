<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HousekeepingTask extends Model
{
    protected $fillable = ['team_id', 'unit_id', 'task_type', 'status'];

    public function unit() { return $this->belongsTo(Unit::class); }
    public function team() { return $this->belongsTo(Team::class); }
}
