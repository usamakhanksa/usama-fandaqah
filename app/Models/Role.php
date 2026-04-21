<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Role extends Model {
  protected $fillable=['name','slug'];
  public function users(){return $this->belongsToMany(\App\User::class);} 
  public function primaryUsers(){return $this->hasMany(\App\User::class);} 
  public function permissions(){return $this->belongsToMany(Permission::class)->withPivot(["enabled","anyone","can_create","can_edit","can_view","can_remove"])->withTimestamps();}
}
