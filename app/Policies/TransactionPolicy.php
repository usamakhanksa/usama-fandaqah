<?php

namespace App\Policies;

use App\User;
use App\Models\Transaction;
use Illuminate\Auth\Access\HandlesAuthorization;

class TransactionPolicy
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
        return $user->can('view transactions');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\User  $user
     * @param  \App\Models\Transaction  $transaction
     * @return mixed
     */
    public function view(User $user, Transaction $transaction)
    {
        return $user->can('view transactions') && $transaction->team_id == $user->current_team_id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->can('create transactions');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\User  $user
     * @param  \App\Models\Transaction  $transaction
     * @return mixed
     */
    public function update(User $user, Transaction $transaction)
    {
        return $user->can('update transactions') && $transaction->team_id == $user->current_team_id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\User  $user
     * @param  \App\Models\Transaction  $transaction
     * @return mixed
     */
    public function delete(User $user, Transaction $transaction)
    {
        return $user->can('delete transactions') && $transaction->team_id == $user->current_team_id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\User  $user
     * @param  \App\Models\Transaction  $transaction
     * @return mixed
     */
    public function restore(User $user, Transaction $transaction)
    {
        return $user->can('restore transactions') && $transaction->team_id == $user->current_team_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function forceDelete(User $user, Transaction $transaction)
    {
        return $user->can('force delete transactions') && $transaction->team_id == $user->current_team_id;
    }

    /**
     * Determine whether the user can reverse the transaction.
     *
     * @param  \App\User  $user
     * @param  \App\Models\Transaction  $transaction
     * @return mixed
     */
    public function reverse(User $user, Transaction $transaction)
    {
        return $user->can('reverse transactions') && $transaction->team_id == $user->current_team_id;
    }

    /**
     * Determine whether the user can export transactions.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function export(User $user)
    {
        return $user->can('export transactions');
    }
}