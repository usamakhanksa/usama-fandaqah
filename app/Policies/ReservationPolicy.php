<?php
namespace App\Policies;
use App\Models\{Reservation,User};
class ReservationPolicy { public function viewAny(User $user): bool { return in_array($user->role?->slug,['super-admin','manager','receptionist']); } }
