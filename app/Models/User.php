<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser {
  use HasApiTokens;
  protected $fillable=['name','email','password','role_id','avatar'];
  protected $hidden=['password'];
  
  public function canAccessPanel(Panel $panel): bool
  {
      return true; // You can add logic here like $this->hasRole('admin')
  }

  public function role(){return $this->belongsTo(Role::class);} 
  public function roles(){return $this->belongsToMany(Role::class);} 
  
  public function teams(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
  {
      return $this->belongsToMany(Team::class, 'team_users');
  }
}
