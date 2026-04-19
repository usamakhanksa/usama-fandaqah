<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Pktharindu\NovaPermissions\Role;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        if ($user->current_team->current_billing_plan == 'team-free' and request('resourceId') == $user->id and $user->hasPermissionTo('view users')) {
            return true;
        }
        else if($user->current_team->current_billing_plan == 'team-free' and request('resourceId') != $user->id and !$user->hasPermissionTo('view users')) {
            return false;   
        }
        return $user->hasPermissionTo('view users');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\User  $user
     * @param  \App\User  $model
     * @return mixed
     */
    public function view(User $user, User $model)
    {
        if($model->onTeam($user->current_team) and $user->hasPermissionTo('view users')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo('create users');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\User  $user
     * @param  \App\User  $model
     * @return mixed
     */
    public function update(User $user, User $model)
    {
        /** @hint prevent user from editing the demo account as he can not change password or email  */
        if($user->current_team_id == 44) return false;
        return $model->onTeam($user->current_team) and $user->hasPermissionTo('edit users');
       
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\User  $user
     * @param  \App\User  $model
     * @return mixed
     */
    public function delete(User $user, User $model)
    {
        return $model->onTeam($user->current_team) and $user->id != $model->id and $user->hasPermissionTo('delete users');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\User  $user
     * @param  \App\User  $model
     * @return mixed
     */
    public function restore(User $user, User $model)
    {
        return $model->onTeam($user->current_team);
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\User  $user
     * @param  \App\User  $model
     * @return mixed
     */
    public function forceDelete(User $user, User $model)
    {
        return $model->onTeam($user->current_team);
    }

    public function attachRole(User $user, User $model, Role $role)
    {
        if($user->isAdmin())
            return true;

        return false;
    }

    public function detachRole(User $user, User $model, Role $role)
    {
        return $user->roles->first()->id != $role->id;
    }
}
