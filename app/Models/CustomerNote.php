<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerNote extends Model
{
    use SoftDeletes;

    protected $fillable = ['team_id', 'customer_id', 'created_by', 'body', 'type'];

    public function createdBy() { return $this->belongsTo(\App\User::class, 'created_by'); }
    public function team()      { return $this->belongsTo(Team::class); }
}
