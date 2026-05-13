<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuickPayment extends Model
{
    protected $fillable = ['team_id', 'amount', 'payment_method', 'reference', 'notes', 'created_by'];

    public function createdBy() { return $this->belongsTo(\App\User::class, 'created_by'); }
    public function team()      { return $this->belongsTo(Team::class); }
}
