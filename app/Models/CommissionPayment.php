<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasTeam;

class CommissionPayment extends Model
{
    use HasFactory, HasTeam, SoftDeletes;

    protected $fillable = [
        'team_id',
        'travel_agent_id',
        'commission_period_from',
        'commission_period_to',
        'payment_number',
        'total_commission',
        'total_paid',
        'payment_method',
        'bank_id',
        'reference_number',
        'payment_date',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'commission_period_from' => 'date',
        'commission_period_to' => 'date',
        'payment_date' => 'date',
        'total_commission' => 'decimal:2',
        'total_paid' => 'decimal:2',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function travelAgent()
    {
        return $this->belongsTo(Company::class, 'travel_agent_id');
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function details()
    {
        return $this->hasMany(CommissionPaymentDetail::class);
    }
}
