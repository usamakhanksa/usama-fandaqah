<?php

namespace App\Policies;

use App\User;
use App\Source;
use Illuminate\Auth\Access\HandlesAuthorization;

class SourcePolicy
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
        return $user->hasPermissionTo('view settings');
    }

    public function view(User $user, Source $source)
    {
        return $user->hasPermissionTo('view settings');
    }

    public function update(User $user, Source $source)
    {
        return $user->hasPermissionTo('view settings');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('view settings');
    }

    public function delete(User $user, Source $source)
    {
        return $user->hasPermissionTo('view settings');
    }

    public function restore(User $user,  Source $source)
    {
        return false;
    }
    
    public function forceDelete(User $user,  Source $source)
    {
        return false;
    }
}
