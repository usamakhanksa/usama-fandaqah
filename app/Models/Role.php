<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Scopes\TeamScope;

class Role extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'deletable',
        'team_id'
    ];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new TeamScope());
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_user');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            config('novapermissions.permission_model'),
            config('novapermissions.role_permission_table'),
            config('novapermissions.role_foreign_key'),
            config('novapermissions.permission_foreign_key')
        );
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}