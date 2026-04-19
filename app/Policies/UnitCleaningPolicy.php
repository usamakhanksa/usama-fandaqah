<?php

namespace App\Policies;

use App\User;
use App\UnitCleaning;
use Illuminate\Auth\Access\HandlesAuthorization;

class UnitCleaningPolicy
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

    public function view(User $user, UnitCleaning $unit)
    {
        return $user->hasPermissionTo('view units');
    }

    public function update(User $user, UnitCleaning $unit)
    {
        return $user->hasPermissionTo('view units');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('view units');
    }

    public function delete(User $user, UnitCleaning $unit)
    {
        return $user->hasPermissionTo('view units');
    }
    
    public function restore(User $user,  UnitCleaning $unit)
    {
        return false;
    }
    
    public function forceDelete(User $user,   UnitCleaning $unit)
    {
        return false;
    }
}
