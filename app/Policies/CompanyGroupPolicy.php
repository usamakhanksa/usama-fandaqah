<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CompanyGroupPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view company groups');
    }

    public function view(User $user, $model)
    {
        return $user->hasPermissionTo('view company groups');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create company groups');
    }

    public function update(User $user, $model)
    {
        return $user->hasPermissionTo('edit company groups');
    }

    public function delete(User $user, $model)
    {
        return $user->hasPermissionTo('delete company groups');
    }

    public function export(User $user)
    {
        return $user->hasPermissionTo('export company groups');
    }

    public function restore(User $user, $model)
    {
        return false;
    }

    public function forceDelete(User $user, $model)
    {
        return false;
    }
}
