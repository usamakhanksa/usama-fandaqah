<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Team;
use App\Models\Reservation;
use App\CheckInRecord;
use App\CheckOutRecord;
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
        $team = Team::where('slug', 'demo-hotel')->first();
        
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
                        'team_id' => $team->id,
                    ], [
                        'reservation_id' => $reservation->id,
                        'team_id' => $team->id,
                        'unit_id' => $reservation->unit_id,
                        'guest_id' => $reservation->guest_id,
                        'actual_check_in_time' => $reservation->check_in->addHours(rand(12, 16))->addMinutes(rand(0, 59)),
                        'checked_in_by' => rand(1, 5), // Random user ID
                        'notes' => 'Guest arrived on time. Room ' . $reservation->unit->number . ' prepared.',
                        'created_at' => $reservation->check_in->addHours(rand(12, 16))->addMinutes(rand(0, 59)),
                        'updated_at' => now(),
                    ]);
                }

                // Create check-out record if the reservation has ended
                if ($reservation->status === 'checked_out' && $reservation->check_out <= Carbon::today()) {
                    // Ensure check-out happens after check-in
                    $checkInTime = $reservation->checkInRecord ? $reservation->checkInRecord->actual_check_in_time : $reservation->check_in;
                    
                    CheckOutRecord::firstOrCreate([
                        'reservation_id' => $reservation->id,
                        'team_id' => $team->id,
                    ], [
                        'reservation_id' => $reservation->id,
                        'team_id' => $team->id,
                        'unit_id' => $reservation->unit_id,
                        'guest_id' => $reservation->guest_id,
                        'actual_check_out_time' => $checkInTime->copy()->addDays(rand(1, 5))->addHours(rand(10, 14))->addMinutes(rand(0, 59)),
                        'checked_out_by' => rand(1, 5), // Random user ID
                        'final_bill_amount' => rand(500, 2000) + ($checkInTime->diffInDays($checkInTime->copy()->addDays(rand(1, 5))) * rand(100, 300)),
                        'outstanding_balance' => $reservation->status === 'checked_out' ? 0 : rand(0, 100),
                        'notes' => 'Guest departed in good condition. Room inspected and ready for next guest.',
                        'created_at' => $checkInTime->copy()->addDays(rand(1, 5))->addHours(rand(10, 14))->addMinutes(rand(0, 59)),
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
                'team_id' => $team->id,
            ], [
                'reservation_id' => $reservation->id,
                'team_id' => $team->id,
                'unit_id' => $reservation->unit_id,
                'guest_id' => $reservation->guest_id,
                'actual_check_in_time' => Carbon::today()->addHours(rand(14, 18))->addMinutes(rand(0, 59)),
                'checked_in_by' => rand(1, 5), // Random user ID
                'notes' => 'Today\'s check-in for reservation ' . $reservation->code . '. Room prepared and key issued.',
                'created_at' => Carbon::today()->addHours(rand(14, 18))->addMinutes(rand(0, 59)),
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
            $checkInRecord = $reservation->checkInRecord;
            $checkInTime = $checkInRecord ? $checkInRecord->actual_check_in_time : $reservation->check_in->addHours(14);
            
            CheckOutRecord::firstOrCreate([
                'reservation_id' => $reservation->id,
                'team_id' => $team->id,
            ], [
                'reservation_id' => $reservation->id,
                'team_id' => $team->id,
                'unit_id' => $reservation->unit_id,
                'guest_id' => $reservation->guest_id,
                'actual_check_out_time' => Carbon::today()->addHours(rand(9, 12))->addMinutes(rand(0, 59)),
                'checked_out_by' => rand(1, 5), // Random user ID
                'final_bill_amount' => rand(500, 1500),
                'outstanding_balance' => 0,
                'notes' => 'Check-out completed for reservation ' . $reservation->code . '. Room inspected.',
                'created_at' => Carbon::today()->addHours(rand(9, 12))->addMinutes(rand(0, 59)),
                'updated_at' => now(),
            ]);
            
            // Update reservation status to checked_out
            $reservation->update(['status' => 'checked_out']);
        }
    }
}