<?php

namespace App\Policies;

use App\User;
use App\UnitGeneralFeature;
use Illuminate\Auth\Access\HandlesAuthorization;

class UnitGeneralFeaturePolicy
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

    public function view(User $user, UnitGeneralFeature $unit)
    {
        return $user->hasPermissionTo('view units');
    }

    public function update(User $user, UnitGeneralFeature $unit)
    {
        return $user->hasPermissionTo('view units');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('view units');
    }

    public function delete(User $user, UnitGeneralFeature $unit)
    {
        return $user->hasPermissionTo('view units');
    }
    
    public function restore(User $user,  UnitGeneralFeature $unit)
    {
        return false;
    }
    
    public function forceDelete(User $user, UnitGeneralFeature $unit)
    {
        return false;
    }
}
