<?php

namespace App\Policies;

use App\User;
use App\UnitOption;
use Illuminate\Auth\Access\HandlesAuthorization;

class UnitOptionPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view units');
    }

    public function view(User $user, UnitOption $unit)
    {
        return $user->hasPermissionTo('view units');
    }

    public function update(User $user, UnitOption $unit)
    {
        return $user->hasPermissionTo('view units');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('view units');
    }

    public function delete(User $user, UnitOption $unit)
    {
        return $user->hasPermissionTo('view units');
    }
    
    public function restore(User $user,  UnitOption $unit)
    {
        return false;
    }
    
    public function forceDelete(User $user,   UnitOption $unit)
    {
        return false;
    }
}
