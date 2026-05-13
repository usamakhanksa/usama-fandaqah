<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CommissionPaymentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view commissions');
    }

    public function view(User $user, $model)
    {
        return $user->hasPermissionTo('view commissions');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create commissions');
    }

    public function update(User $user, $model)
    {
        return $user->hasPermissionTo('edit commissions');
    }

    public function approve(User $user)
    {
        return $user->hasPermissionTo('approve commissions');
    }

    public function export(User $user)
    {
        return $user->hasPermissionTo('export commissions');
    }

    public function delete(User $user, $model)
    {
        return false; // Commission records are never deleted
    }
}
