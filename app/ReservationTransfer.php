<?php

namespace App;

use App\Traits\HasTeam;
use Watson\Rememberable\Rememberable;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;
use App\Scopes\TeamScope;

class ReservationTransfer extends Model
{
    use LogsActivity;
    use Rememberable;
    use HasTeam;

    protected $fillable = [
        'created_by',
        'old_unit_id',
        'new_unit_id',
        'old_date_in',
        'old_date_in',
        'old_date_out',
        'new_date_in',
        'new_date_out',
        'old_price',
        'new_price',
        'reason',
        'reservation_id',
        'team_id'
    ];

    protected static $logAttributes = ['old_unit.unit_number', 'new_unit.unit_number' , 'reservation.number' , 'old_price' , 'new_price' , 'reason'];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new TeamScope());
    }

    /**
     * @return BelongsTo
     */
    public function reservation()
    {
        return $this->belongsTo(Reservation::class, 'reservation_id')->withTrashed();
    }

    /**
     * @return BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    /**
     * @return BelongsTo
     */
    public function old_unit()
    {
        return $this->belongsTo(Unit::class, 'old_unit_id')->withTrashed();
    }

    /**
     * @return BelongsTo
     */
    public function new_unit()
    {
        return $this->belongsTo(Unit::class, 'new_unit_id')->withTrashed();
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "نقل الحجز رقم {$this->reservation->number} من الوحدة رقم {$this->old_unit->unit_number} إلى الوحدة رقم {$this->new_unit->unit_number}";
    }
}
