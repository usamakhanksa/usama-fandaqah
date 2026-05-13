<?php

namespace App\Policies;

use App\Models\FormIntegration;
use App\Models\User;

class FormIntegrationPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, FormIntegration $formIntegration): bool
    {
        return $user->currentTeam->id === $formIntegration->team_id;
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
    public function update(User $user, FormIntegration $formIntegration): bool
    {
        return $user->currentTeam->id === $formIntegration->team_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, FormIntegration $formIntegration): bool
    {
        return $user->currentTeam->id === $formIntegration->team_id;
    }
}
