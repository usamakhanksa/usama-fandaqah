<?php

namespace App;

use App\Traits\HasTeam;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Watson\Rememberable\Rememberable;
use App\Scopes\TeamScope;

class Rating extends Model
{
    use Rememberable, SoftDeletes, HasTeam;

    protected $fillable = [
        'q_one',
        'q_two',
        'q_three',
        'q_four',
        'q_five',
        'q_six',
        'q_seven',
        'q_eight',
        'q_nine',
        'q_ten',
        'q_eleven',
        'q_twelve',
        'q_custom',

    ];

    protected static function boot()
    {
        parent::boot();
        // static::addGlobalScope(new TeamScope());
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class)->withoutGlobalScopes()->withTrashed();
    }

}
