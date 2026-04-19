<?php

namespace App;

use App\Traits\HasTeam;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;
use Watson\Rememberable\Rememberable;
use App\Scopes\TeamScope;

class Note extends Model
{
    use Rememberable;
    use LogsActivity, HasTeam;

    protected $fillable = [
    	'body', 'created_by_id', 'team_id', 'day_id'
    ];


    protected static $logAttributes = ['body'];
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new TeamScope());
    }

    /**
     * @return BelongsTo
     */
    public function day()
    {
        return $this->belongsTo(NoteDay::class, 'day_id');
    }

    /**
     * @return BelongsTo
     */
    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }
}
