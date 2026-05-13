<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\Source;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OTAReservationsTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $team;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->team = Team::create(['name' => 'OTA Test Team']);
        $this->user = User::create([
            'name' => 'OTA Manager',
            'email' => 'ota@example.com',
            'password' => bcrypt('password'),
            'current_team_id' => $this->team->id
        ]);
        $this->user->teams()->attach($this->team->id, ['role' => 'admin']);
    }

    public function test_ota_reservations_list_shows_correct_data()
    {
        // 1. Create sources (one OTA, one local)
        $otaSource = Source::create([
            'team_id' => $this->team->id,
            'name' => ['en' => 'Booking.com', 'ar' => 'بوكينج'],
            'is_travel_agent' => true
        ]);
        $localSource = Source::create([
            'team_id' => $this->team->id,
            'name' => ['en' => 'Walk-In', 'ar' => 'مباشر'],
            'is_travel_agent' => false
        ]);

        $room = Room::create([
            'team_id' => $this->team->id,
            'number' => 'OTA-101',
            'status' => 'available'
        ]);
        $guest = Guest::create([
            'team_id' => $this->team->id,
            'name' => 'OTA Guest',
            'phone' => '555-OTA'
        ]);

        // 2. Create reservations
        Reservation::create([
            'team_id' => $this->team->id,
            'code' => 'OTA-RES-001',
            'guest_id' => $guest->id,
            'room_id' => $room->id,
            'source_id' => $otaSource->id,
            'status' => 'confirmed',
            'check_in' => now()->toDateString(),
            'check_out' => now()->addDays(2)->toDateString(),
        ]);

        Reservation::create([
            'team_id' => $this->team->id,
            'code' => 'LOCAL-RES-001',
            'guest_id' => $guest->id,
            'room_id' => $room->id,
            'source_id' => $localSource->id,
            'status' => 'confirmed',
            'check_in' => now()->toDateString(),
            'check_out' => now()->addDays(2)->toDateString(),
        ]);

        // 3. Test list (should only show the OTA one)
        $response = $this->actingAs($this->user)
            ->getJson('/api/reservations/ota');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.code', 'OTA-RES-001');

        // 4. Test filters
        $response = $this->actingAs($this->user)
            ->getJson('/api/reservations/ota?search=LOCAL');
        
        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    public function test_can_sync_ota_status()
    {
        $otaSource = Source::create([
            'team_id' => $this->team->id,
            'name' => ['en' => 'Expedia', 'ar' => 'اكسبيديا'],
            'is_travel_agent' => true
        ]);
        $room = Room::create(['team_id' => $this->team->id, 'number' => 'OTA-102']);
        $guest = Guest::create(['team_id' => $this->team->id, 'name' => 'Sync Guest']);

        $res = Reservation::create([
            'team_id' => $this->team->id,
            'code' => 'OTA-SYNC-001',
            'guest_id' => $guest->id,
            'room_id' => $room->id,
            'source_id' => $otaSource->id,
            'status' => 'pending',
            'check_in' => now()->toDateString(),
            'check_out' => now()->addDays(1)->toDateString(),
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/reservations/{$res->id}/sync-status");

        $response->assertStatus(200);
    }
}
