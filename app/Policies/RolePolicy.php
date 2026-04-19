<?php

namespace App\Policies;

use App\Role;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RolePolicy
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


    /**
     * Determine whether the user can view any teams.
     *
     * @param \App\User $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        // if ($user->current_team->current_billing_plan == 'team-free') {
        //     return false;
        // }

        if($user->isAdmin())
            return true;

        return $user->hasPermissionTo('view roles');
    }

    /**
     * Determine whether the user can view the team.
     *
     * @param \App\User $user
     * @param \App\Team $team
     * @return mixed
     */
    public function view(User $user, Role $role)
    {
        if($user->isAdmin())
            return true;

        return $user->hasPermissionTo('view roles');
    }

    /**
     * Determine whether the user can create teams.
     *
     * @param \App\User $user
     * @return mixed
     */
    public function create(User $user)
    {
        
        if($user->isAdmin())
            return true;
        
        return $user->hasPermissionTo('view roles');
    }

    /**
     * Determine whether the user can update the team.
     *
     * @param \App\User $user
     * @param \App\Team $team
     * @return mixed
     */
    public function update(User $user, Role $role)
    {
        if ($role->slug == 'admin' || !$user->isAdmin() || $user->currentTeam->current_billing_plan == 'trial') {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can delete the team.
     *
     * @param \App\User $user
     * @param \App\Team $team
     * @return mixed
     */
    public function delete(User $user, Role $role)
    {
        if ($role->slug == 'admin' || !$user->isAdmin()) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can restore the team.
     *
     * @param \App\User $user
     * @param \App\Team $team
     * @return mixed
     */
    public function restore(User $user, Role $role)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the team.
     *
     * @param \App\User $user
     * @param \App\Team $team
     * @return mixed
     */
    public function forceDelete(User $user, Role $role)
    {
        //
    }

    public function attachUser(User $user, Role $role, User $model)
    {
        if($user->isAdmin())
            return true;

        return false;
    }

    public function detachUser(User $user, Role $role, User $handler)
    {
        return $handler->roles->first()->id != $role->id;

        if ($role->slug == 'admin' && count($role->users) == 1)
            return false;
        return true;
    }
}
