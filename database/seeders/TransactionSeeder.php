<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Team;
use App\Models\Reservation;
use Carbon\Carbon;

class TransactionSeeder extends Seeder
{
    public function run()
    {
        $team = Team::where('slug', 'fandaqah-palace')->first();
        if (!$team) return;

        $reservations = Reservation::where('team_id', $team->id)->take(10)->get();
        if ($reservations->isEmpty()) return;

        foreach ($reservations as $res) {
            // Room Charge (Withdraw/Debit)
            DB::table('transactions')->insert([
                'team_id' => $team->id,
                'payable_type' => 'App\Models\Reservation',
                'payable_id' => $res->id,
                'type' => 'withdraw',
                'kind' => 'room_charge',
                'amount' => 500.00,
                'amount_without_tax' => 434.78,
                'tax_amount' => 65.22,
                'tax_percentage' => 15,
                'business_date' => $team->business_date ?? now()->toDateString(),
                'description' => 'Daily room charge for ' . $res->code,
                'created_at' => now(),
                'updated_at' => now(),
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
            ]);

            // Payment (Deposit/Credit)
            DB::table('transactions')->insert([
                'team_id' => $team->id,
                'payable_type' => 'App\Models\Reservation',
                'payable_id' => $res->id,
                'type' => 'deposit',
                'kind' => 'payment',
                'amount' => 500.00,
                'amount_without_tax' => 500.00,
                'tax_amount' => 0.00,
                'tax_percentage' => 0,
                'business_date' => $team->business_date ?? now()->toDateString(),
                'description' => 'Payment for ' . $res->code,
                'meta' => json_encode(['payment_type' => 'cash']),
                'created_at' => now(),
                'updated_at' => now(),
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
            ]);
        }
    }
}