<?php

namespace App\Policies;

use App\Models\DashboardNotice;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DashboardNoticePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, DashboardNotice $dashboardNotice): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('manage dashboard');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DashboardNotice $dashboardNotice): bool
    {
        return $user->hasPermission('manage dashboard');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DashboardNotice $dashboardNotice): bool
    {
        return $user->hasPermission('manage dashboard');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, DashboardNotice $dashboardNotice): bool
    {
        return $user->hasPermission('manage dashboard');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, DashboardNotice $dashboardNotice): bool
    {
        return $user->hasPermission('manage dashboard');
    }
}
