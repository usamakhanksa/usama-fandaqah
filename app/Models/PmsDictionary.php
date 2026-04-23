<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PmsDictionary extends Model {
    use SoftDeletes;
    protected $fillable = ['group', 'label', 'meta', 'is_active'];
    protected $casts = [
        'meta' => 'json',
        'is_active' => 'boolean'
    ];

    public function scopeActive($query) {
        return $query->where('is_active', true);
    }
}
