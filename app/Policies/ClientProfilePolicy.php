<?php

namespace App\Policies;

use App\Models\ClientProfile;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ClientProfilePolicy {
    use HandlesAuthorization;
    public function viewAny(User $user): bool { return true; }
    public function view(User $user, ClientProfile $clientRelation): bool { return true; }
    public function create(User $user): bool { return true; }
    public function update(User $user, ClientProfile $clientRelation): bool { return true; }
    public function delete(User $user, ClientProfile $clientRelation): bool { return true; }
}
