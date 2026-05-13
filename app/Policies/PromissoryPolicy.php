<?php

namespace App\Policies;

use App\User;
use App\Models\Promissory;
use Illuminate\Auth\Access\HandlesAuthorization;

class PromissoryPolicy
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
        return $user->can('view promissory notes');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\User  $user
     * @param  \App\Models\Promissory  $promissory
     * @return mixed
     */
    public function view(User $user, Promissory $promissory)
    {
        return $user->can('view promissory notes') && $promissory->team_id == $user->current_team_id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->can('create promissory notes');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\User  $user
     * @param  \App\Models\Promissory  $promissory
     * @return mixed
     */
    public function update(User $user, Promissory $promissory)
    {
        return $user->can('update promissory notes') && $promissory->team_id == $user->current_team_id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\User  $user
     * @param  \App\Models\Promissory  $promissory
     * @return mixed
     */
    public function delete(User $user, Promissory $promissory)
    {
        return $user->can('delete promissory notes') && $promissory->team_id == $user->current_team_id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\User  $user
     * @param  \App\Models\Promissory  $promissory
     * @return mixed
     */
    public function restore(User $user, Promissory $promissory)
    {
        return $user->can('restore promissory notes') && $promissory->team_id == $user->current_team_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function forceDelete(User $user, Promissory $promissory)
    {
        return $user->can('force delete promissory notes') && $promissory->team_id == $user->current_team_id;
    }

    /**
     * Determine whether the user can collect the promissory note.
     *
     * @param  \App\User  $user
     * @param  \App\Models\Promissory  $promissory
     * @return mixed
     */
    public function collect(User $user, Promissory $promissory)
    {
        return $user->can('collect promissory notes') && $promissory->team_id == $user->current_team_id;
    }

    /**
     * Determine whether the user can export promissory notes.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function export(User $user)
    {
        return $user->can('export promissory notes');
    }
}