<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'team_id',
        'name',
        'phone',
        'email',
        'id_number',
        'id_expire_date',
        'birthday_date',
        'gender',
        'country_id',
        'id_type',
        'work',
        'address',
        'customer_category_type',
    ];

    /**
     * Get the team that owns the customer.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
