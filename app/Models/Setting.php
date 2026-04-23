<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model {
    protected $fillable = ['group', 'key', 'payload'];
    protected $casts = ['payload' => 'array'];

    public static function getGroup($group) {
        return self::where('group', $group)->get()->pluck('payload', 'key')->toArray();
    }
}
