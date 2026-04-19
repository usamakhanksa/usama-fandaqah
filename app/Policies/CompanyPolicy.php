<?php

namespace App\Policies;

use App\User;
use App\Company;
use Illuminate\Auth\Access\HandlesAuthorization;

class CompanyPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {

    }
    
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view companies');
    }

    public function view(User $user, Company $company)
    {
        return $user->hasPermissionTo('view company profile');
    }

    public function update(User $user, Company $company)
    {
        return $user->hasPermissionTo('update companies');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create companies');
    }

    public function delete(User $user, Company $company)
    {
        return $user->hasPermissionTo('update companies');
    }

    public function restore(User $user,  Company $company)
    {
        return false;
    }

    public function forceDelete(User $user,  Company $company)
    {
        return false;
    }
}
