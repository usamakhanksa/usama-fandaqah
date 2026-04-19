<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PromoCodeLog extends Model
{
    protected $fillable = ['team_id', 'user_id', 'promo_code_id'];
}
