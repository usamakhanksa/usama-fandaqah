<?php

namespace App\Policies;

use App\User;
use App\Service;
use Illuminate\Auth\Access\HandlesAuthorization;

class ServicePolicy
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

    public function view(User $user, Service $service)
    {
        return $user->hasPermissionTo('view services');
    }

    public function update(User $user, Service $service)
    {
        return $user->hasPermissionTo('view services');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('add services');
    }

    public function delete(User $user, Service $service)
    {
        return $user->hasPermissionTo('view services');
    }

    public function restore(User $user,  Service $service)
    {
        return false;
    }
    
    public function forceDelete(User $user,  Service $service)
    {
        return false;
    }
}
