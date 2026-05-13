<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Reservation;
use App\Models\Team;
use App\Models\Unit;
use App\Models\ReservationRoomTransfer;
use App\User;

class ReservationTransferSeeder extends Seeder
{
    public function run()
    {
        $team = Team::where('slug', 'fandaqah-palace')->first();
        if (!$team) return;

        $reservations = Reservation::where('team_id', $team->id)->limit(5)->get();
        $units = Unit::where('team_id', $team->id)->limit(10)->get();
        $user = User::where('current_team_id', $team->id)->first();

        if ($reservations->isEmpty() || $units->count() < 2) return;

        foreach ($reservations as $reservation) {
            $fromUnit = $reservation->unit_id ?? $units[0]->id;
            $toUnit = $units->where('id', '!=', $fromUnit)->random()->id;

            ReservationRoomTransfer::create([
                'team_id' => $team->id,
                'reservation_id' => $reservation->id,
                'from_unit_id' => $fromUnit,
                'to_unit_id' => $toUnit,
                'reason' => 'Guest requested higher floor',
                'created_by' => $user ? $user->id : 1,
                'created_at' => now()->subDays(rand(1, 10)),
            ]);
        }
    }
}
