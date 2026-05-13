<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitCategoryService extends Model
{
    protected $table = 'unit_category_services';

    protected $fillable = ['team_id', 'unit_category_id', 'service_id', 'is_included', 'price_override', 'is_active'];

    public function unitCategory() { return $this->belongsTo(\App\UnitCategory::class); }
    public function service()      { return $this->belongsTo(Service::class); }
    public function team()         { return $this->belongsTo(Team::class); }
}
