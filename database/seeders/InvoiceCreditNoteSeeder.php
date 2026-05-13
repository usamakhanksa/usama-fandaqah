<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Team;
use App\Models\Reservation;

class InvoiceCreditNoteSeeder extends Seeder
{
    public function run()
    {
        $team = Team::where('slug', 'fandaqah-palace')->first();
        if (!$team) return;

        $reservations = Reservation::where('team_id', $team->id)->take(5)->get();

        foreach ($reservations as $res) {
            // 1. Create a Booking first
            $bookingId = DB::table('bookings')->insertGetId([
                'team_id' => $team->id,
                'reservation_id' => $res->id,
                'guest_id' => $res->guest_id,
                'room_id' => $res->room_id,
                'check_in' => $res->check_in,
                'check_out' => $res->check_out,
                'total_amount' => $res->total_price ?? 1500.00,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Create the Invoice linked to the Booking
            DB::table('invoices')->insert([
                'booking_id' => $bookingId,
                'number' => 'INV-' . rand(10000, 99999),
                'amount' => $res->total_price ?? 1500.00,
                'status' => 'paid',
                'is_zatca_reported' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}