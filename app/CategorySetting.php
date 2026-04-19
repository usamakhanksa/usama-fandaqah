<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class CategorySetting extends Model
{
    protected $fillable = [
        'category_id',
        'sunday_price',
        'monday_price',
        'tuesday_price',
        'wednesday_price',
        'thursday_price',
        'friday_price',
        'saturday_price',
        'virtual_rooms',
        'second_rateplan_operator',
        'second_rateplan_value',
    ];

    public function category()
    {
        return $this->belongsTo(UnitCategory::class);
    }

}
