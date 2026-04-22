<?php

namespace App\Policies\Foundation;

use App\Models\User;

class FoundationPolicy
{
    public function viewAny(?User $user = null): bool { return true; }
    public function view(?User $user = null): bool { return true; }
    public function create(?User $user = null): bool { return true; }
    public function update(?User $user = null): bool { return true; }
    public function delete(?User $user = null): bool { return true; }
}
