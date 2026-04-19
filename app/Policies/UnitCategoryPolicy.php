<?php

namespace App\Policies;

use App\User;
use App\UnitCategory;
use Illuminate\Auth\Access\HandlesAuthorization;

class UnitCategoryPolicy
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

    public function view(User $user, UnitCategory $unit)
    {
        return $user->hasPermissionTo('view units');
    }

    public function update(User $user, UnitCategory $unit)
    {
        return $user->hasPermissionTo('view units');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('view units');
    }

    public function delete(User $user, UnitCategory $unit)
    {
        return $user->hasPermissionTo('view units');
    }

    public function restore(User $user,  UnitCategory $unit)
    {
        return false;
    }
    
    public function forceDelete(User $user,   UnitCategory $unit)
    {
        return false;
    }
}
