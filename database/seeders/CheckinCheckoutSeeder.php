<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Team;
use App\Models\Reservation;
use App\Models\CheckInRecord;
use App\Models\CheckOutRecord;
use Carbon\Carbon;

class CheckinCheckoutSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get the demo team
        $team = Team::where('slug', 'fandaqah-palace')->first() ?: Team::where('slug', 'demo-hotel')->first();
        
        if (!$team) {
            $this->command->error('Demo team not found. Please run TeamSeeder first.');
            return;
        }

        // Get reservations
        $reservations = Reservation::where('team_id', $team->id)
            ->whereIn('status', ['checked_in', 'checked_out'])
            ->get();
        
        if ($reservations->count() === 0) {
            $this->command->error('No checked-in or checked-out reservations found. Please run ReservationSeeder first.');
            return;
        }

        foreach ($reservations as $reservation) {
            if ($reservation->status === 'checked_in' || $reservation->status === 'checked_out') {
                // Create check-in record if the reservation has started
                if ($reservation->check_in <= Carbon::today()) {
                    CheckInRecord::firstOrCreate([
                        'reservation_id' => $reservation->id,
                    ], [
                        'reservation_id' => $reservation->id,
                        'unit_id' => $reservation->unit_id,
                        'date' => $reservation->check_in->toDateString(),
                        'time' => $reservation->check_in->format('H:i:s'),
                        'note' => 'Guest arrived on time. Room prepared.',
                        'created_at' => $reservation->check_in,
                        'updated_at' => now(),
                    ]);
                }

                // Create check-out record if the reservation has ended
                if ($reservation->status === 'checked_out' && $reservation->check_out <= Carbon::today()) {
                    CheckOutRecord::firstOrCreate([
                        'reservation_id' => $reservation->id,
                    ], [
                        'reservation_id' => $reservation->id,
                        'unit_id' => $reservation->unit_id,
                        'date' => $reservation->check_out->toDateString(),
                        'time' => $reservation->check_out->format('H:i:s'),
                        'note' => 'Guest departed in good condition. Room inspected.',
                        'final_charges' => $reservation->total_price ?: rand(500, 2000),
                        'created_at' => $reservation->check_out,
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // Create some additional check-in records for reservations that should be checked in today
        $todaysCheckIns = Reservation::where('team_id', $team->id)
            ->where('status', 'confirmed')
            ->whereDate('check_in', Carbon::today())
            ->get();

        foreach ($todaysCheckIns as $reservation) {
            CheckInRecord::firstOrCreate([
                'reservation_id' => $reservation->id,
            ], [
                'reservation_id' => $reservation->id,
                'unit_id' => $reservation->unit_id,
                'date' => Carbon::today()->toDateString(),
                'time' => Carbon::now()->format('H:i:s'),
                'note' => 'Today\'s check-in for reservation ' . $reservation->code . '. Room prepared and key issued.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Update reservation status to checked_in
            $reservation->update(['status' => 'checked_in']);
        }

        // Create some check-out records for reservations ending today
        $todaysCheckOuts = Reservation::where('team_id', $team->id)
            ->where('status', 'checked_in')
            ->whereDate('check_out', Carbon::today())
            ->get();

        foreach ($todaysCheckOuts as $reservation) {
            CheckOutRecord::firstOrCreate([
                'reservation_id' => $reservation->id,
            ], [
                'reservation_id' => $reservation->id,
                'unit_id' => $reservation->unit_id,
                'date' => Carbon::today()->toDateString(),
                'time' => Carbon::now()->format('H:i:s'),
                'note' => 'Check-out completed for reservation ' . $reservation->code . '. Room inspected.',
                'final_charges' => $reservation->total_price ?: rand(500, 1500),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Update reservation status to checked_out
            $reservation->update(['status' => 'checked_out']);
        }
    }
}