<?php

namespace Tests\Feature;

use App\Models\Reservation;
use App\Models\Booking;
use App\Models\FinancialRecord;
use App\Models\Unit;
use App\Models\UnitStatus;
use App\Team;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutBalanceTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $team;

    protected function setUp(): void
    {
        parent::setUp();
        $this->team = Team::create(['name' => 'Test Team']);
        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'current_team_id' => $this->team->id,
        ]);
        
        UnitStatus::create(['name' => 'Occupied', 'slug' => 'occupied', 'color' => '#ff0000']);
        UnitStatus::create(['name' => 'Available', 'slug' => 'available', 'color' => '#00ff00']);
    }

    public function test_checkout_fails_with_unresolved_balance()
    {
        $reservation = $this->createReservationWithBalance(100);

        $response = $this->actingAs($this->user)->postJson('/api/units/check-out', [
            'reservation_id' => $reservation->id,
            'unit_id' => $reservation->unit_id,
            'date' => now()->toDateString(),
            'time' => '12:00 PM',
        ]);

        $response->assertStatus(422);
        $response->assertJsonFragment(['requires_resolution' => true]);
    }

    public function test_checkout_succeeds_with_promissory_resolution()
    {
        $reservation = $this->createReservationWithBalance(500);

        $response = $this->actingAs($this->user)->postJson('/api/units/check-out', [
            'reservation_id' => $reservation->id,
            'unit_id' => $reservation->unit_id,
            'date' => now()->toDateString(),
            'time' => '12:00 PM',
            'resolution_type' => 'signed_promissory',
            'resolution_amount' => 500,
            'promissory_due_date' => now()->addDays(30)->toDateString(),
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('promissories', [
            'reservation_id' => $reservation->id,
            'total_amount' => 500,
            'signature_status' => 'signed',
        ]);
        $this->assertDatabaseHas('checkout_balance_transfers', [
            'reservation_id' => $reservation->id,
            'resolution_type' => 'signed_promissory',
        ]);
    }

    public function test_checkout_fails_with_unsigned_promissory_without_reason()
    {
        $reservation = $this->createReservationWithBalance(500);

        $response = $this->actingAs($this->user)->postJson('/api/units/check-out', [
            'reservation_id' => $reservation->id,
            'unit_id' => $reservation->unit_id,
            'date' => now()->toDateString(),
            'time' => '12:00 PM',
            'resolution_type' => 'unsigned_promissory',
            'resolution_amount' => 500,
            'promissory_due_date' => now()->addDays(30)->toDateString(),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['unsigned_reason']);
    }

    protected function createReservationWithBalance($balance)
    {
        $unit = Unit::create([
            'team_id' => $this->team->id,
            'name' => 'Room 101',
            'number' => '101',
            'unit_status_id' => UnitStatus::where('slug', 'occupied')->first()->id,
        ]);

        $booking = Booking::create([
            'team_id' => $this->team->id,
            'total_amount' => $balance,
        ]);

        $reservation = Reservation::create([
            'team_id' => $this->team->id,
            'unit_id' => $unit->id,
            'booking_id' => $booking->id,
            'check_in' => now()->subDays(2)->toDateString(),
            'check_out' => now()->toDateString(),
            'stay_type' => 'checkin',
        ]);

        FinancialRecord::create([
            'team_id' => $this->team->id,
            'booking_id' => $booking->id,
            'label' => 'Stay Charge',
            'amount' => $balance,
            'type' => 'charge',
        ]);

        return $reservation;
    }
}
