<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'team_id',
        'name',
        'status',
        'show_in_reservation',
        'show_in_pos',
        'order',
        'users',
        'rev_type'
    ];

    protected $casts = [
        'status' => 'integer',
        'show_in_reservation' => 'boolean',
        'show_in_pos' => 'boolean',
        'order' => 'integer',
        'users' => 'array',
    ];

    public function getNameAttribute($value)
    {
        return json_decode($value, true) ?: ['en' => $value, 'ar' => $value];
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = is_array($value) ? json_encode($value) : $value;
    }
}
