<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NightAuditSnapshot extends Model
{
    use HasFactory;

    protected $table = 'night_audit_snapshots';

    protected $fillable = [
        'team_id',
        'business_date',
        'snapshot_type',
        'room_status',
        'guest_counts',
        'revenue_summary',
        'occupancy_data',
        'created_by',
    ];

    protected $casts = [
        'room_status' => 'array',
        'guest_counts' => 'array',
        'revenue_summary' => 'array',
        'occupancy_data' => 'array',
        'business_date' => 'date',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
?>
