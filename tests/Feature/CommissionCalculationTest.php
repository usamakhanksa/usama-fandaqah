<?php

namespace Tests\Feature;

use App\Models\CommissionPayment;
use App\Models\Reservation;
use App\Source;
use App\Team;
use App\User;
use App\Services\CommissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommissionCalculationTest extends TestCase
{
    use RefreshDatabase;

    public function test_commission_is_calculated_correctly_for_percentage()
    {
        $team = Team::create(['name' => 'Test Team']);
        $source = Source::create([
            'name' => ['en' => 'Test Agent'],
            'is_travel_agent' => true,
            'commission_rate' => 10,
            'commission_type' => 'percentage',
            'team_id' => $team->id
        ]);

        $reservation = Reservation::create([
            'team_id' => $team->id,
            'source_id' => $source->id,
            'total_price' => 1000,
            'check_in' => now(),
            'check_out' => now()->addDays(2),
            'stay_type' => 'checkin',
            'code' => 'TEST-123'
        ]);

        $service = new CommissionService();
        $payment = $service->processReservationCommission($reservation);

        $this->assertEquals(100, $payment->commission_amount);
        $this->assertEquals('pending', $payment->status);
    }

    public function test_commission_is_calculated_correctly_for_fixed()
    {
        $team = Team::create(['name' => 'Fixed Team']);
        $source = Source::create([
            'name' => ['en' => 'Fixed Agent'],
            'is_travel_agent' => true,
            'commission_rate' => 50,
            'commission_type' => 'fixed',
            'team_id' => $team->id
        ]);

        $reservation = Reservation::create([
            'team_id' => $team->id,
            'source_id' => $source->id,
            'total_price' => 1000,
            'check_in' => now(),
            'check_out' => now()->addDays(2),
            'stay_type' => 'checkin',
            'code' => 'TEST-456'
        ]);

        $service = new CommissionService();
        $payment = $service->processReservationCommission($reservation);

        $this->assertEquals(50, $payment->commission_amount);
    }

    public function test_commission_approval_flow()
    {
        $user = User::create(['name' => 'Admin', 'email' => 'admin@test.com', 'password' => bcrypt('password')]);
        $payment = CommissionPayment::create([
            'source_id' => 1,
            'reservation_id' => 1,
            'team_id' => 1,
            'commission_amount' => 100,
            'status' => 'pending'
        ]);

        $service = new CommissionService();
        $service->approveCommission($payment, $user->id);

        $this->assertEquals('approved', $payment->status);
        $this->assertEquals($user->id, $payment->approved_by);
    }
}
