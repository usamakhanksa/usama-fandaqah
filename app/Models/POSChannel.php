<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class POSChannel extends Model
{
    protected $table = 'p_o_s_channels';
    protected $guarded = [];

    public function stores(): HasMany
    {
        return $this->hasMany(POSStore::class, 'p_o_s_channel_id');
    }
}
