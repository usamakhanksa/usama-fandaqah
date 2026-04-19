<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
class User extends Authenticatable {
  use HasApiTokens;
  protected $fillable=['name','email','password','role_id','avatar'];
  protected $hidden=['password'];
  public function role(){return $this->belongsTo(Role::class);} 
  public function roles(){return $this->belongsToMany(Role::class);} 
}
