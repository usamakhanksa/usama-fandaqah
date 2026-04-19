<?php

namespace App\Policies;

use App\User;
use App\UnitSpecialFeature;
use Illuminate\Auth\Access\HandlesAuthorization;

class UnitSpecialFeaturePolicy
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

    public function view(User $user, UnitSpecialFeature $unit)
    {
        return $user->hasPermissionTo('view units');
    }

    public function update(User $user, UnitSpecialFeature $unit)
    {
        return $user->hasPermissionTo('view units');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('view units');
    }

    public function delete(User $user, UnitSpecialFeature $unit)
    {
        return $user->hasPermissionTo('view units');
    }
    
    public function restore(User $user,  UnitSpecialFeature $unit)
    {
        return false;
    }
    
    public function forceDelete(User $user,   UnitSpecialFeature $unit)
    {
        return false;
    }
}
