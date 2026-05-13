<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\Team;
use App\Models\User;
use App\GroupReservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupReservationsTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $team;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::create([
            'name' => 'Group Manager',
            'email' => 'group' . rand(1, 999) . '@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->team = Team::create([
            'name' => 'Group Test Team',
            'owner_id' => $this->user->id
        ]);

        $this->user->update(['current_team_id' => $this->team->id]);
    }

    public function test_group_reservation_crud_works()
    {
        // 1. Create dependencies
        $company = Company::create([
            'team_id' => $this->team->id,
            'name' => 'Test Corp'
        ]);
        $room1 = Room::create(['team_id' => $this->team->id, 'number' => 'G101', 'status' => 'available']);
        $room2 = Room::create(['team_id' => $this->team->id, 'number' => 'G102', 'status' => 'available']);

        // 2. Create Group (POST)
        $payload = [
            'name' => 'Conference Group',
            'company_id' => $company->id,
            'room_ids' => [$room1->id, $room2->id],
            'check_in' => now()->toDateString(),
            'check_out' => now()->addDays(3)->toDateString(),
            'rate' => 500,
            'instructions' => 'VIP Group'
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/reservations/groups', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('group_reservations', [
            'company_id' => $company->id,
        ]);
        $this->assertEquals(2, Reservation::count());

        // 3. List Groups (GET)
        $response = $this->actingAs($this->user)
            ->getJson('/api/reservations/groups');
        
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Conference Group');

        // 4. Cancel Group (POST)
        $group = GroupReservation::first();
        $response = $this->actingAs($this->user)
            ->postJson("/api/reservations/groups/{$group->id}/cancel");

        $response->assertStatus(200);
        $this->assertEquals('cancelled', Reservation::first()->status);
    }
}
