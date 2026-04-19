<?php

namespace App;

use App\Traits\HasTeam;
use Illuminate\Database\Eloquent\Model;
use Watson\Rememberable\Rememberable;
use App\Scopes\TeamScope;

class NoteDay extends Model
{
    use Rememberable;
	use HasTeam;

    protected $fillable = [
    	'day'
    ];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new TeamScope());
    }

    /**
     * @return BelongsTo
     */
    public function notes()
    {
        return $this->hasMany(Note::class, 'day_id')->orderByDesc('created_at');
    }
}
