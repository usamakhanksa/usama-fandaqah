<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MergeLog extends Model
{
    protected $fillable = ['team_id', 'primary_customer_id', 'merged_customer_id', 'fields_kept', 'merged_by'];
    protected $casts = ['fields_kept' => 'array'];

    public function mergedBy() { return $this->belongsTo(\App\User::class, 'merged_by'); }
    public function team()     { return $this->belongsTo(Team::class); }
}
