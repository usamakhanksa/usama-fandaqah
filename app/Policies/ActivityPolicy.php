<?php

namespace App\Policies;

use App\User;
use App\Activity;
use Illuminate\Auth\Access\HandlesAuthorization;

class ActivityPolicy
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
        return $user->isAdmin();
    }

    public function view(User $user, Activity $activity)
    {
        return $user->isAdmin();
    }

    public function update(User $user, Activity $activity)
    {
        return true;
    }

    public function create(User $user)
    {
        return true;
    }

    public function delete(User $user, Activity $activity)
    {
        return false;
    }

    public function restore(User $user,  Activity $activity)
    {
        return false;
    }

    public function forceDelete(User $user,  Activity $activity)
    {
        return false;
    }
}
