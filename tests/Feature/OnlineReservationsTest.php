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

class OnlineReservationsTest extends TestCase
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
            'current_team_id' => $this->team->id
        ]);
        $this->user->teams()->attach($this->team->id, ['role' => 'admin']);
    }

    public function test_online_reservations_list_filters_correctly()
    {
        // 1. Create sources (one online, one offline)
        $onlineSource = Source::create([
            'team_id' => $this->team->id,
            'name' => ['en' => 'Booking.com', 'ar' => 'بوكينج']
        ]);
        $offlineSource = Source::create([
            'team_id' => $this->team->id,
            'name' => ['en' => 'Front Desk', 'ar' => 'الاستقبال']
        ]);

        $room = Room::create([
            'team_id' => $this->team->id,
            'number' => '101',
            'status' => 'available'
        ]);
        $guest = Guest::create([
            'team_id' => $this->team->id,
            'name' => 'John Doe',
            'phone' => '123456789'
        ]);

        // 2. Create reservations
        $onlineRes = Reservation::create([
            'team_id' => $this->team->id,
            'code' => 'ONLINE-001',
            'guest_id' => $guest->id,
            'room_id' => $room->id,
            'source_id' => $onlineSource->id,
            'status' => 'pending',
            'check_in' => now()->addDays(1)->toDateString(),
            'check_out' => now()->addDays(3)->toDateString(),
        ]);

        $offlineRes = Reservation::create([
            'team_id' => $this->team->id,
            'code' => 'OFFLINE-001',
            'guest_id' => $guest->id,
            'room_id' => $room->id,
            'source_id' => $offlineSource->id,
            'status' => 'confirmed',
            'check_in' => now()->addDays(1)->toDateString(),
            'check_out' => now()->addDays(3)->toDateString(),
        ]);

        // 3. Test list without filters
        $response = $this->actingAs($this->user)
            ->getJson('/api/reservations/online');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.code', 'ONLINE-001');

        // 4. Test filter by status
        $response = $this->actingAs($this->user)
            ->getJson('/api/reservations/online?status=confirmed');
        
        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');

        $onlineRes->update(['status' => 'confirmed']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/reservations/online?status=confirmed');
        
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');

        // 5. Test filter by date
        $date = now()->addDays(1)->toDateString();
        $response = $this->actingAs($this->user)
            ->getJson("/api/reservations/online?date={$date}");
        
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');

        $date = now()->addDays(10)->toDateString();
        $response = $this->actingAs($this->user)
            ->getJson("/api/reservations/online?date={$date}");
        
        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    public function test_can_confirm_online_reservation()
    {
        $onlineSource = Source::create([
            'team_id' => $this->team->id,
            'name' => ['en' => 'Booking.com', 'ar' => 'بوكينج']
        ]);
        $room = Room::create([
            'team_id' => $this->team->id,
            'number' => '102',
            'status' => 'available'
        ]);
        $guest = Guest::create([
            'team_id' => $this->team->id,
            'name' => 'Jane Doe',
            'phone' => '987654321'
        ]);

        $res = Reservation::create([
            'team_id' => $this->team->id,
            'code' => 'ONLINE-002',
            'guest_id' => $guest->id,
            'room_id' => $room->id,
            'source_id' => $onlineSource->id,
            'status' => 'pending',
            'check_in' => now()->addDays(1)->toDateString(),
            'check_out' => now()->addDays(3)->toDateString(),
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/reservations/{$res->id}/confirm");

        $response->assertStatus(200);
        $this->assertEquals('confirmed', $res->fresh()->status);
    }
}
