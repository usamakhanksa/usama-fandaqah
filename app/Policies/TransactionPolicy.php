<?php

namespace App\Policies;

use App\User;
use App\Transaction;
use Illuminate\Auth\Access\HandlesAuthorization;

class TransactionPolicy
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
        return $user->hasPermissionTo('view financial');
    }

    public function view(User $user, Transaction $service)
    {
        return $user->hasPermissionTo('view financial');
    }

    public function update(User $user, Transaction $service)
    {
        return $user->hasPermissionTo('edit financial');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create financial');
    }

    public function delete(User $user, Transaction $service)
    {
        return $user->hasPermissionTo('delete financial');
    }

    public function restore(User $user,  Transaction $service)
    {
        return false;
    }
    
    public function forceDelete(User $user,  Service $service)
    {
        return false;
    }
}
