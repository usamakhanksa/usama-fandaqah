<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class RecentActivity extends Model {
    protected $fillable = ['type', 'description', 'user_id', 'icon', 'color'];
    public function user() { return $this->belongsTo(\App\User::class); }
}
