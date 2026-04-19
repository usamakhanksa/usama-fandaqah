<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = ['name', 'slug', 'group'];

    public function roles()
    {
        return $this->belongsToMany(Role::class)
            ->withPivot(['enabled', 'anyone', 'can_create', 'can_edit', 'can_view', 'can_remove'])
            ->withTimestamps();
    }
}
