<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Receipt;
use App\Models\Reservation;
use App\Models\Guest;
use App\Models\Company;
use App\Models\Team;
use App\User;
use Carbon\Carbon;

class ReceiptSeeder extends Seeder
{
    public function run(): void
    {
        $teams = Team::take(3)->get();
        if ($teams->isEmpty()) {
            $this->command->warn('No teams found. Skipping receipt seeding.');
            return;
        }

        foreach ($teams as $team) {
            $this->seedReceiptsForTeam($team);
        }

        $this->command->info('Receipts seeded successfully across teams.');
    }

    private function seedReceiptsForTeam(Team $team)
    {
        $users = User::where('current_team_id', $team->id)->get();
        if ($users->isEmpty()) return;

        $reservations = Reservation::where('team_id', $team->id)->take(20)->get();
        $guests = Guest::where('team_id', $team->id)->take(20)->get();
        $companies = Company::where('team_id', $team->id)->take(5)->get();

        // 35 confirmed, 10 draft, 5 cancelled = 50 total
        $statuses = array_merge(
            array_fill(0, 35, 'confirmed'),
            array_fill(0, 10, 'draft'),
            array_fill(0, 5, 'cancelled')
        );
        shuffle($statuses);

        // 20 cash, 15 card, 10 bank_transfer, 3 cheque, 2 online = 50 total
        $methods = array_merge(
            array_fill(0, 20, 'cash'),
            array_fill(0, 15, 'card'),
            array_fill(0, 10, 'bank_transfer'),
            array_fill(0, 3, 'cheque'),
            array_fill(0, 2, 'online')
        );
        shuffle($methods);

        for ($i = 0; $i < 50; $i++) {
            $status = $statuses[$i];
            $method = $methods[$i];
            $user = $users->random();
            
            // Special case for USD receipts (3 per team)
            $isUSD = ($i < 3);
            $currency = $isUSD ? 'USD' : 'SAR';
            $rate = $isUSD ? 3.7500 : 1.0000;
            
            $receipt = Receipt::create([
                'team_id' => $team->id,
                'reservation_id' => $reservations->isNotEmpty() ? $reservations->random()->id : null,
                'guest_id' => $guests->isNotEmpty() ? $guests->random()->id : null,
                'company_id' => $companies->isNotEmpty() ? $companies->random()->id : null,
                'receipt_number' => "RCP-" . now()->format('Ym') . "-" . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'receipt_date' => Carbon::now()->subDays(rand(1, 90))->toDateString(),
                'amount' => rand(100, 5000),
                'payment_method' => $method,
                'currency' => $currency,
                'exchange_rate' => $rate,
                'reference_number' => in_array($method, ['bank_transfer', 'online']) ? 'REF-' . rand(1000, 9999) : null,
                'bank_name' => $method === 'cheque' ? 'Riyad Bank' : null,
                'cheque_number' => $method === 'cheque' ? 'CHQ-' . rand(1000, 9999) : null,
                'card_last_four' => $method === 'card' ? rand(1000, 9999) : null,
                'description' => 'Demo receipt for ' . $team->name,
                'status' => $status,
                'created_by' => $user->id,
                'cancelled_at' => $status === 'cancelled' ? now() : null,
                'cancelled_by' => $status === 'cancelled' ? $user->id : null,
                'cancellation_reason' => $status === 'cancelled' ? 'Customer request' : null,
            ]);
        }
    }
}
