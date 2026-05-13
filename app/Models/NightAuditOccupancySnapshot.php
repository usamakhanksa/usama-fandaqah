<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NightAuditOccupancySnapshot extends Model
{
    protected $table = 'night_audit_occupancy_snapshot';
    public $timestamps = false;

    protected $fillable = [
        'team_id', 'business_date', 'run_number', 'is_final',
        'total_rooms', 'rooms_available', 'rooms_occupied',
        'rooms_cleaning', 'rooms_maintenance', 'rooms_complimentary',
        'rooms_house_use', 'rooms_day_use', 'is_backfill',
        'occupancy_pct', 'adr', 'revpar', 'arrivals_count',
        'departures_count', 'stayovers_count', 'noshows_count',
        'cancellations_count', 'new_bookings_count', 'room_revenue',
        'room_revenue_complimentary', 'service_revenue', 'noshow_revenue',
        'adjustment_revenue', 'rebate_amount', 'total_revenue',
        'vat_total', 'ewa_total', 'total_deposits_collected',
        'total_promissory_created', 'total_promissory_collected',
        'outstanding_promissory_balance', 'adults_count', 'children_count'
    ];
}
