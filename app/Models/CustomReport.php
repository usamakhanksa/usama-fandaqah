<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomReport extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'team_id',
        'name',
        'description',
        'module',
        'columns',
        'filters',
        'sort_by',
        'sort_direction',
        'group_by',
        'is_shared',
        'created_by',
    ];

    protected $casts = [
        'columns' => 'array',
        'filters' => 'array',
        'is_shared' => 'boolean',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function schedules()
    {
        return $this->hasMany(ReportSchedule::class);
    }
}
