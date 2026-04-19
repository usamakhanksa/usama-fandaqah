<?php

namespace App\Policies;

use App\User;
use App\Unit;
use Illuminate\Auth\Access\HandlesAuthorization;

class UnitPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        
    }

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view units');
    }

    public function view(User $user, Unit $unit)
    {
        return $user->hasPermissionTo('view units');
    }

    public function update(User $user, Unit $unit)
    {
        return $user->hasPermissionTo('view units');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('view units');
    }

    public function delete(User $user, Unit $unit)
    {
        return $user->hasPermissionTo('view units');
    }

    public function restore(User $user,  Unit $unit)
    {
        return false;
    }
    
    public function forceDelete(User $user,   Unit $unit)
    {
        return false;
    }
}
