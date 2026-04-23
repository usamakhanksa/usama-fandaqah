<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InventoryPolicy {
    use HandlesAuthorization;
    public function manage(User $user): bool { return true; }
}
