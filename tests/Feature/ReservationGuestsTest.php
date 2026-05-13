<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\Reservation;
use App\Models\Team;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class ReservationGuestsTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $team;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::create([
            'name' => 'Manager',
            'email' => 'manager' . rand(1, 999) . '@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->team = Team::create([
            'name' => 'Test Hotel',
            'owner_id' => $this->user->id
        ]);

        $this->user->update(['current_team_id' => $this->team->id]);
    }

    public function test_guest_crud_works()
    {
        // 1. Create dependencies
        $reservation = Reservation::create([
            'team_id' => $this->team->id,
            'code' => 'RES123',
            'check_in' => now(),
            'check_out' => now()->addDays(2),
            'status' => 'confirmed'
        ]);
        
        $guest = Guest::create([
            'team_id' => $this->team->id,
            'full_name' => 'John Doe',
            'id_type' => 'Passport',
            'id_number' => 'ABC12345'
        ]);

        // 2. Add Guest (POST)
        $response = $this->actingAs($this->user)
            ->postJson("/api/reservations/{$reservation->id}/guests", [
                'guest_id' => $guest->id,
                'is_primary' => true
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('reservation_guests', [
            'reservation_id' => $reservation->id,
            'guest_id' => $guest->id
        ]);

        // 3. List Guests (GET)
        $response = $this->actingAs($this->user)
            ->getJson("/api/reservations/{$reservation->id}/guests");
        
        $response->assertStatus(200)
            ->assertJsonCount(1);

        // 4. Remove Guest (DELETE)
        $response = $this->actingAs($this->user)
            ->deleteJson("/api/reservations/{$reservation->id}/guests/{$guest->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('reservation_guests', [
            'reservation_id' => $reservation->id,
            'guest_id' => $guest->id
        ]);
    }
}
