<?php

namespace App\Policies;

use App\Models\ApiConsumer;
use App\Models\User;

class ApiConsumerPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ApiConsumer $consumer): bool
    {
        return $user->currentTeam->id === $consumer->team_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ApiConsumer $consumer): bool
    {
        return $user->currentTeam->id === $consumer->team_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ApiConsumer $consumer): bool
    {
        return $user->currentTeam->id === $consumer->team_id;
    }
}
