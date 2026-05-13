<?php

namespace App\Policies;

use App\Models\CashierShift;
use App\User;
use Illuminate\Auth\Access\Response;

class CashierShiftPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('cashier.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CashierShift $cashierShift): bool
    {
        return $user->hasPermissionTo('cashier.view') && $user->team_id === $cashierShift->team_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('cashier.open_shift');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CashierShift $cashierShift): bool
    {
        // For closing the shift
        return $user->hasPermissionTo('cashier.close_shift') && 
               $user->id === $cashierShift->user_id && 
               $cashierShift->status === CashierShift::STATUS_OPEN;
    }

    /**
     * Determine whether the user can approve the model.
     */
    public function approve(User $user, CashierShift $cashierShift): bool
    {
        return $user->hasPermissionTo('cashier.approve_shift') && 
               $cashierShift->status === CashierShift::STATUS_CLOSED;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CashierShift $cashierShift): bool
    {
        return false; // Shifts should not be deleted
    }
}
