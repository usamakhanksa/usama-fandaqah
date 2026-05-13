<?php

namespace App\Policies;

use App\User;
use App\Models\ServiceLog;
use Illuminate\Auth\Access\HandlesAuthorization;

class ServiceLogPolicy
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
        return $user->can('view service logs');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\User  $user
     * @param  \App\Models\ServiceLog  $serviceLog
     * @return mixed
     */
    public function view(User $user, ServiceLog $serviceLog)
    {
        return $user->can('view service logs') && $serviceLog->team_id == $user->current_team_id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->can('create service logs');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\User  $user
     * @param  \App\Models\ServiceLog  $serviceLog
     * @return mixed
     */
    public function update(User $user, ServiceLog $serviceLog)
    {
        return $user->can('update service logs') && $serviceLog->team_id == $user->current_team_id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\User  $user
     * @param  \App\Models\ServiceLog  $serviceLog
     * @return mixed
     */
    public function delete(User $user, ServiceLog $serviceLog)
    {
        return $user->can('delete service logs') && $serviceLog->team_id == $user->current_team_id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\User  $user
     * @param  \App\Models\ServiceLog  $serviceLog
     * @return mixed
     */
    public function restore(User $user, ServiceLog $serviceLog)
    {
        return $user->can('restore service logs') && $serviceLog->team_id == $user->current_team_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function forceDelete(User $user, ServiceLog $serviceLog)
    {
        return $user->can('force delete service logs') && $serviceLog->team_id == $user->current_team_id;
    }

    /**
     * Determine whether the user can export service logs.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function export(User $user)
    {
        return $user->can('export service logs');
    }
}