<?php

namespace App\Policies;

use App\User;
use App\Models\Reservation;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReservationPolicy
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
        return $user->can('view reservations');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\User  $user
     * @param  \App\Models\Reservation  $reservation
     * @return mixed
     */
    public function view(User $user, Reservation $reservation)
    {
        return $user->can('view reservations') && $reservation->team_id == $user->current_team_id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->can('create reservations');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\User  $user
     * @param  \App\Models\Reservation  $reservation
     * @return mixed
     */
    public function update(User $user, Reservation $reservation)
    {
        return $user->can('update reservations') && $reservation->team_id == $user->current_team_id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\User  $user
     * @param  \App\Models\Reservation  $reservation
     * @return mixed
     */
    public function delete(User $user, Reservation $reservation)
    {
        return $user->can('delete reservations') && $reservation->team_id == $user->current_team_id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\User  $user
     * @param  \App\Models\Reservation  $reservation
     * @return mixed
     */
    public function restore(User $user, Reservation $reservation)
    {
        return $user->can('restore reservations') && $reservation->team_id == $user->current_team_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\User  $user
     * @param  \App\Models\Reservation  $reservation
     * @return mixed
     */
    public function forceDelete(User $user, Reservation $reservation)
    {
        return $user->can('force delete reservations') && $reservation->team_id == $user->current_team_id;
    }

    /**
     * Determine whether the user can check in.
     *
     * @param  \App\User  $user
     * @param  \App\Models\Reservation  $reservation
     * @return mixed
     */
    public function checkIn(User $user, Reservation $reservation)
    {
        return $user->can('checkin reservations') && $reservation->team_id == $user->current_team_id;
    }

    /**
     * Determine whether the user can check out.
     *
     * @param  \App\User  $user
     * @param  \App\Models\Reservation  $reservation
     * @return mixed
     */
    public function checkOut(User $user, Reservation $reservation)
    {
        return $user->can('checkout reservations') && $reservation->team_id == $user->current_team_id;
    }

    /**
     * Determine whether the user can cancel reservation.
     *
     * @param  \App\User  $user
     * @param  \App\Models\Reservation  $reservation
     * @return mixed
     */
    public function cancel(User $user, Reservation $reservation)
    {
        return $user->can('cancel reservations') && $reservation->team_id == $user->current_team_id;
    }

    /**
     * Determine whether the user can extend reservation.
     *
     * @param  \App\User  $user
     * @param  \App\Models\Reservation  $reservation
     * @return mixed
     */
    public function extend(User $user, Reservation $reservation)
    {
        return $user->can('extend reservations') && $reservation->team_id == $user->current_team_id;
    }

    /**
     * Determine whether the user can transfer reservation.
     *
     * @param  \App\User  $user
     * @param  \App\Models\Reservation  $reservation
     * @return mixed
     */
    public function transfer(User $user, Reservation $reservation)
    {
        return $user->can('transfer reservations') && $reservation->team_id == $user->current_team_id;
    }

    /**
     * Determine whether the user can approve reservation.
     *
     * @param  \App\User  $user
     * @param  \App\Models\Reservation  $reservation
     * @return mixed
     */
    public function approve(User $user, Reservation $reservation)
    {
        return $user->can('approve reservations') && $reservation->team_id == $user->current_team_id;
    }

    /**
     * Determine whether the user can export reservations.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function export(User $user)
    {
        return $user->can('export reservations');
    }

    /**
     * Determine whether the user can import reservations.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function import(User $user)
    {
        return $user->can('import reservations');
    }
}