<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Team;
use App\Models\Reservation;
use App\Guest;
use App\DigitalSignature;
use Carbon\Carbon;

class ReservationGuestSeeder extends Seeder
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

        // Get reservations and create guests linked to them
        $reservations = Reservation::where('team_id', $team->id)->get();
        
        if ($reservations->count() === 0) {
            $this->command->error('No reservations found. Please run ReservationSeeder first.');
            return;
        }

        // Create guests for each reservation
        foreach ($reservations as $index => $reservation) {
            // Create guest record linked to the reservation
            $guest = Guest::firstOrCreate([
                'team_id' => $team->id,
                'email' => 'guest'.$index.'@reservation.com',
            ], [
                'name' => 'Guest for '.$reservation->code,
                'email' => 'guest'.$index.'@reservation.com',
                'phone' => '+96650000000'.$index,
                'type' => 'individual',
                'gender' => $index % 2 === 0 ? 'male' : 'female',
                'id_type' => $index % 3 === 0 ? 'national_id' : ($index % 3 === 1 ? 'passport' : 'iqama'),
                'id_number' => 'ID'.$index.str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                'nationality' => $index % 4 === 0 ? 1 : ($index % 4 === 1 ? 2 : ($index % 4 === 2 ? 3 : 4)), // Reference to country ID
                'address' => 'Address for guest '.$index,
                'date_of_birth' => Carbon::now()->subYears(rand(20, 60)),
                'shomoos_verified_at' => $reservation->shomoos_verification_status === 'verified' ? Carbon::now()->subDays(rand(1, 10)) : null,
                'shomoos_reference' => $reservation->shomoos_verification_status === 'verified' ? 'SHM'.rand(100000, 999999) : null,
                'shomoos_status' => $reservation->shomoos_verification_status,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Update reservation to point to the guest
            $reservation->update(['guest_id' => $guest->id]);
            
            // If the reservation requires a digital signature, create one
            if (in_array($reservation->reservation_category_type, ['vip', 'corporate', 'government'])) {
                DigitalSignature::firstOrCreate([
                    'team_id' => $team->id,
                    'ref_id' => $reservation->id,
                    'type' => DigitalSignature::TYPE_RESERVATION,
                ], [
                    'team_id' => $team->id,
                    'ref_id' => $reservation->id,
                    'type' => DigitalSignature::TYPE_RESERVATION,
                    'signature_base64' => base64_encode(gzcompress('Sample signature data for reservation '.$reservation->code)),
                    'user_id' => null, // For customer signature
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                // Create official signature as well
                DigitalSignature::firstOrCreate([
                    'team_id' => $team->id,
                    'ref_id' => $reservation->id,
                    'type' => DigitalSignature::TYPE_RESERVATION,
                    'user_id' => 1, // For official signature
                ], [
                    'team_id' => $team->id,
                    'ref_id' => $reservation->id,
                    'type' => DigitalSignature::TYPE_RESERVATION,
                    'signature_base64' => base64_encode(gzcompress('Official signature for reservation '.$reservation->code)),
                    'user_id' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}