<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FinancialPolicy {
    use HandlesAuthorization;

    public function viewAny(User $user): bool {
        return true; // Simplified for PMS demo
    }

    public function view(User $user): bool {
        return true;
    }

    public function create(User $user): bool {
        return true;
    }

    public function update(User $user): bool {
        return true;
    }

    public function delete(User $user): bool {
        return true;
    }

    public function manage(User $user): bool {
        return true;
    }
}
