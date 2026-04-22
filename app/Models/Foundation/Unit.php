<?php

namespace App\Models\Foundation;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Database\Factories\Foundation\UnitFactory;

class Unit extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'units';

    protected $fillable = [
        'unit_category_id',
        'name',
        'number',
        'code',
        'floor_no',
        'max_occupancy',
        'current_rate_sar',
        'status',
    ];

    protected $casts = [
        'current_rate_sar' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(UnitCategory::class, 'unit_category_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    protected static function newFactory(): UnitFactory
    {
        return UnitFactory::new();
    }
}
