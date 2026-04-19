<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class POSStore extends Model
{
    protected $table = 'p_o_s_stores';
    protected $guarded = [];

    public function channel(): BelongsTo
    {
        return $this->belongsTo(POSChannel::class, 'p_o_s_channel_id');
    }
}
