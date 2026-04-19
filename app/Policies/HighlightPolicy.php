<?php

namespace App\Policies;

use App\User;
use App\Highlight;
use Illuminate\Auth\Access\HandlesAuthorization;

class HighlightPolicy
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

    public function view(User $user, Highlight $highlight)
    {
        return $user->hasPermissionTo('view settings');
    }

    public function update(User $user, Highlight $highlight)
    {
        return $user->hasPermissionTo('view settings');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('view settings');
    }

    public function delete(User $user, Highlight $highlight)
    {
        return $user->hasPermissionTo('view settings');
    }

    public function restore(User $user,  Highlight $highlight)
    {
        return false;
    }
    
    public function forceDelete(User $user,  Highlight $highlight)
    {
        return false;
    }
}
