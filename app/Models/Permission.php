<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = ['name', 'slug', 'group', 'module'];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permission', 'permission_slug', 'role_id')
            ->withPivot(['created_at', 'updated_at']);
    }
}