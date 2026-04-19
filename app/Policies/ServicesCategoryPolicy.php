<?php

namespace App\Policies;

use App\User;
use App\ServicesCategory;
use Illuminate\Auth\Access\HandlesAuthorization;

class ServicesCategoryPolicy
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
        return $user->hasPermissionTo('view services');
    }

    public function view(User $user, ServicesCategory $service)
    {
        return $user->hasPermissionTo('view services');
    }

    public function update(User $user, ServicesCategory $service)
    {
        return $user->hasPermissionTo('view services');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('view services');
    }

    public function delete(User $user, ServicesCategory $service)
    {
        return $user->hasPermissionTo('view services');
    }

    public function restore(User $user,  ServicesCategory $service)
    {
        return false;
    }
    
    public function forceDelete(User $user,  ServicesCategory $service)
    {
        return false;
    }
}
