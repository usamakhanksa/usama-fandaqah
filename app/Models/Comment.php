<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = ['team_id', 'commentable_type', 'commentable_id', 'user_id', 'body'];

    public function commentable() { return $this->morphTo(); }
    public function user()        { return $this->belongsTo(\App\User::class); }
    public function team()        { return $this->belongsTo(Team::class); }
}
