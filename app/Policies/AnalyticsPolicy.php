<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AnalyticsPolicy {
    use HandlesAuthorization;
    public function manage(User $user): bool { return true; } // Generic access for demonstration
}
