<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BookingPolicy {
    use HandlesAuthorization;
    public function viewAny(User $user): bool { return true; }
    public function view(User $user, Booking $booking): bool { return true; }
    public function create(User $user): bool { return true; }
    public function update(User $user, Booking $booking): bool { return true; }
    public function delete(User $user, Booking $booking): bool { return true; }
}
