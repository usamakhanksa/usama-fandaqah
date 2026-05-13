<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SidebarItem extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'active_route_patterns' => 'array',
        'is_visible' => 'boolean',
        'is_beta' => 'boolean',
        'is_external' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(SidebarItem::class, 'parent_key', 'item_key');
    }

    public function children()
    {
        return $this->hasMany(SidebarItem::class, 'parent_key', 'item_key')->orderBy('order');
    }
}
