<?php

namespace App\Policies;

use App\Models\ApiToken;
use App\Models\User;

class ApiTokenPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ApiToken $token): bool
    {
        return $user->currentTeam->id === $token->team_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ApiToken $token): bool
    {
        return $user->currentTeam->id === $token->team_id;
    }
}
