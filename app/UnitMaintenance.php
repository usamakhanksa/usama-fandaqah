<?php

namespace App;

use App\Traits\HasTeam;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Watson\Rememberable\Rememberable;
use DateTime;
use App\Scopes\TeamScope;

class UnitMaintenance extends Model
{
    use Rememberable;
    use LogsActivity, HasTeam;

    protected $fillable = ['start_at', 'completed_at', 'completed_by', 'created_by', 'note', 'unit_id', 'team_id', 'action_id', 'expected_at'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'completed_at' => 'datetime',
        'start_at' => 'datetime'
    ];

    protected $appends = [
        'action_type'
    ];
    /**
     * @return BelongsTo
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    /**
     * @return BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }


    /**
     * @return BelongsTo
     */
    public function completedBy()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    /**
     * Get the user's first name.
     *
     * @param  string  $value
     * @return string
     */
    public function getTimeSpentAttribute($value)
    {
        if(isset($this->completed_at)){
            $interval = $this->completed_at->diff($this->start_at);
            return $interval->format('%a '.__('days').' %h '.__('hours').' %i '.__('minutes'));
        }
        return null;
    }

    public function getExpectedDateTimeAttribute() {
        if(isset($this->expected_at)) {
            $date = new DateTime($this->expected_at);
            $formattedDate = $date->format('Y-m-d h:i:s A');
            return $formattedDate;
        }
        return null;
    }

    /**
     * Scope for Valid Timeslots available, doesnot started yet Slots/Appointments
     * @param  [type] $query [description]
     * @return [type]        [description]
     */
    public function scopeStatus($query, $status = null )
    {
        if($status == 'inprogress'){
            return $query->whereNull('completed_at');
        }elseif ($status == 'completed') {
            return $query->whereNotNull('completed_at');
        }
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new TeamScope()); 

        static::creating(function ($item) {
            if (\Auth::check()) {
                $item->created_by = auth()->user()->id;
                $item->team_id = $item->team_id ?? auth()->user()->current_team_id;
            }
        });

    }


     public function getActionTypeAttribute() {
        return ActionType::find($this->action_id);
     }
}
