<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class NightAuditPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view night audit');
    }

    public function view(User $user, $model)
    {
        return $user->hasPermissionTo('view night audit');
    }

    public function run(User $user)
    {
        return $user->hasPermissionTo('run night audit');
    }

    public function rerun(User $user)
    {
        return $user->hasPermissionTo('rerun night audit');
    }

    public function export(User $user)
    {
        return $user->hasPermissionTo('export night audit');
    }
}
