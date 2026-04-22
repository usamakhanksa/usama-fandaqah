<?php

namespace App\Models\Foundation;

use App\Unit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Database\Factories\Foundation\UnitCategoryFactory;

class UnitCategory extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name_en',
        'name_ar',
        'code',
        'default_capacity',
        'base_rate_sar',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'base_rate_sar' => 'decimal:2',
    ];

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class, 'unit_category_id');
    }

    protected static function newFactory(): UnitCategoryFactory
    {
        return UnitCategoryFactory::new();
    }
}
