<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Scopes\TeamScope;

class GuestComment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'team_id',
        'guest_id',
        'comment_type',
        'comment',
        'is_internal',
        'priority',
        'created_by'
    ];

    protected $casts = [
        'is_internal' => 'boolean'
    ];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new TeamScope());
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
