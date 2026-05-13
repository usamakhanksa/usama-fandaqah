<?php

namespace App\Policies;

use App\Models\IntegrationLog;
use App\Models\User;

class IntegrationLogPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, IntegrationLog $log): bool
    {
        return $user->currentTeam->id === $log->team_id;
    }
}
