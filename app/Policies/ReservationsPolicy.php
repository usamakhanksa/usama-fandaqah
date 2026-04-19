<?php

namespace App\Policies;

use App\Reservation;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReservationsPolicy
{
    use HandlesAuthorization;



    /**
     * Determine whether the user can view the post.
     *
     * @param \App\User $user
     * @param \App\Reservation $reservation
     *
     * @return mixed
     */
    public function viewAny(User $user)
    {

        return $user->hasPermissionTo('view reservations');
    }


    /**
     * Determine whether the user can view the post.
     *
     * @param \App\User $user
     * @param \App\Reservation $reservation
     *
     * @return mixed
     */
    public function view(User $user, Reservation $reservation)
    {
        if ($user->hasPermissionTo('view own reservations')) {
            return $user->id === $reservation->user_id;
        }

        return $user->hasPermissionTo('view reservations');
    }

    /**
     * @param User $user
     * @return bool
     * @throws \Exception
     */
    public function create(User $user)
    {
        return $user->hasAnyPermission(['create reservations', 'create own reservations']);
    }

    /**
     * Determine whether the user can update the post.
     *
     * @param \App\User $user
     * @param \App\Reservation $reservation
     *
     * @return mixed
     */
    public function update(User $user, Reservation $reservation)
    {
        if ($user->hasPermissionTo('manage own posts')) {
            return $user->id == $reservation->user_id;
        }
        return $user->hasPermissionTo('edit reservations');
    }

    /**
     * Determine whether the user can delete the post.
     *
     * @param \App\User $user
     * @param \App\Reservation $reservation
     *
     * @return mixed
     */
    public function delete(User $user, Reservation $reservation)
    {
        if ($user->hasPermissionTo('delete own reservations')) {
            return $user->id === $reservation->user_id;
        }

        return $user->hasPermissionTo('delete reservations');
    }
}
