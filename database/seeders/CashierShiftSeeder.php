<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CashierShift;
use App\Models\Team;
use App\User;
use Carbon\Carbon;

class CashierShiftSeeder extends Seeder
{
    public function run()
    {
        $team = Team::first();
        if (!$team) return;

        $users = User::where('current_team_id', $team->id)->take(3)->get();
        if ($users->isEmpty()) return;

        // 20 closed + approved
        for ($i = 0; $i < 20; $i++) {
            $user = $users->random();
            $openedAt = now()->subDays(30 - $i)->setHour(8);
            $closedAt = (clone $openedAt)->addHours(8);
            
            $openingBalance = 500.00;
            $received = rand(1000, 5000);
            $paid = rand(0, 200);
            $expected = $openingBalance + $received - $paid;
            $actual = $expected; // no variance for most

            CashierShift::create([
                'team_id' => $team->id,
                'user_id' => $user->id,
                'shift_number' => 'SH-' . strtoupper(uniqid()),
                'opened_at' => $openedAt,
                'closed_at' => $closedAt,
                'opening_balance' => $openingBalance,
                'expected_closing_balance' => $expected,
                'actual_closing_balance' => $actual,
                'variance' => 0,
                'total_cash_received' => $received * 0.4,
                'total_card_received' => $received * 0.6,
                'total_transactions' => rand(10, 50),
                'status' => CashierShift::STATUS_APPROVED,
                'approved_by' => $users->first()->id,
                'approved_at' => (clone $closedAt)->addHour(),
            ]);
        }

        // 5 closed + pending
        for ($i = 0; $i < 5; $i++) {
            $user = $users->random();
            $openedAt = now()->subDays(2 + $i)->setHour(8);
            $closedAt = (clone $openedAt)->addHours(8);
            
            $openingBalance = 500.00;
            $received = rand(1000, 3000);
            $expected = $openingBalance + $received;
            $actual = $expected + (rand(0, 1) ? 10 : -10); // small variance

            CashierShift::create([
                'team_id' => $team->id,
                'user_id' => $user->id,
                'shift_number' => 'SH-' . strtoupper(uniqid()),
                'opened_at' => $openedAt,
                'closed_at' => $closedAt,
                'opening_balance' => $openingBalance,
                'expected_closing_balance' => $expected,
                'actual_closing_balance' => $actual,
                'variance' => $actual - $expected,
                'variance_reason' => $actual != $expected ? 'Counting error' : null,
                'status' => CashierShift::STATUS_PENDING,
            ]);
        }

        // 3 open
        foreach ($users as $index => $user) {
            CashierShift::create([
                'team_id' => $team->id,
                'user_id' => $user->id,
                'shift_number' => 'SH-' . strtoupper(uniqid()),
                'opened_at' => now()->startOfDay()->addHours(8 + $index),
                'opening_balance' => 500.00,
                'status' => CashierShift::STATUS_OPEN,
            ]);
        }

        // 2 rejected
        for ($i = 0; $i < 2; $i++) {
            $user = $users->random();
            $openedAt = now()->subDays(10 + $i)->setHour(8);
            $closedAt = (clone $openedAt)->addHours(8);

            CashierShift::create([
                'team_id' => $team->id,
                'user_id' => $user->id,
                'shift_number' => 'SH-' . strtoupper(uniqid()),
                'opened_at' => $openedAt,
                'closed_at' => $closedAt,
                'opening_balance' => 500.00,
                'expected_closing_balance' => 1500.00,
                'actual_closing_balance' => 1200.00,
                'variance' => -300.00,
                'variance_reason' => 'Missing cash from drawer',
                'status' => CashierShift::STATUS_REJECTED,
                'rejected_by' => $users->first()->id,
                'rejected_at' => (clone $closedAt)->addHour(),
                'rejection_reason' => 'Large variance without sufficient explanation',
            ]);
        }
    }
}