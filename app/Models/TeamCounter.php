<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamCounter extends Model
{
    protected $fillable = ['team_id', 'type', 'prefix', 'value'];
}
