<?php

namespace Tests\Feature;

use App\Models\Reservation;
use App\Models\StayChargeConfig;
use App\Models\Booking;
use App\Team;
use App\Services\StayChargeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StayChargeServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new StayChargeService();
    }

    public function test_calculate_early_checkin_charge()
    {
        $team = Team::create(['name' => 'Test Team']);
        
        StayChargeConfig::create([
            'team_id' => $team->id,
            'charge_type' => 'early_checkin',
            'tier_from_hour' => '08:00:00',
            'tier_to_hour' => '12:00:00',
            'rate_type' => 'fixed',
            'rate_amount' => 150,
            'applies_to' => 'all',
            'is_active' => true,
        ]);

        $reservation = Reservation::create([
            'team_id' => $team->id,
            'check_in' => now()->addDays(1)->toDateString(),
            'check_out' => now()->addDays(3)->toDateString(),
            'total_price' => 1000,
        ]);

        $charge = $this->service->calculateCharge($reservation, '09:30:00', 'early_checkin');

        $this->assertEquals(150, $charge);
    }

    public function test_calculate_percentage_charge()
    {
        $team = Team::create(['name' => 'Test Team 2']);
        
        StayChargeConfig::create([
            'team_id' => $team->id,
            'charge_type' => 'late_checkout',
            'tier_from_hour' => '14:00:00',
            'tier_to_hour' => '18:00:00',
            'rate_type' => 'percentage_nightly_rate',
            'rate_amount' => 50,
            'applies_to' => 'all',
            'is_active' => true,
        ]);

        $reservation = Reservation::create([
            'team_id' => $team->id,
            'check_in' => '2026-05-01',
            'check_out' => '2026-05-02', // 1 night
            'total_price' => 500,
        ]);
        
        // Mocking total_amount from booking if needed, but service uses total_price as fallback
        
        $charge = $this->service->calculateCharge($reservation, '15:00:00', 'late_checkout');

        // 50% of 500 = 250
        $this->assertEquals(250, $charge);
    }

    public function test_overlap_detection()
    {
        $team = Team::create(['name' => 'Test Team 3']);
        
        StayChargeConfig::create([
            'team_id' => $team->id,
            'charge_type' => 'early_checkin',
            'tier_from_hour' => '08:00:00',
            'tier_to_hour' => '10:00:00',
            'rate_type' => 'fixed',
            'rate_amount' => 100,
            'applies_to' => 'all',
            'is_active' => true,
        ]);

        $hasOverlap = $this->service->checkOverlaps($team->id, 'early_checkin', '09:00', '11:00', 'all');
        $this->assertTrue($hasOverlap);

        $noOverlap = $this->service->checkOverlaps($team->id, 'early_checkin', '10:01', '12:00', 'all');
        $this->assertFalse($noOverlap);
    }
}
