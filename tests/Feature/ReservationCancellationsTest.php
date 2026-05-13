<?php

namespace Tests\Feature;

use App\Models\Reservation;
use App\Models\Team;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReservationCancellationsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Team $team;

    protected function setUp(): void
    {
        parent::setUp();
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $this->user = User::create([
            'name' => 'Manager',
            'email' => 'manager' . rand(1, 9999) . '@test.com',
            'password' => bcrypt('password'),
        ]);

        $this->team = Team::create([
            'name' => 'Test Hotel',
            'owner_id' => $this->user->id,
        ]);

        $this->user->update(['current_team_id' => $this->team->id]);
    }

    protected function makeReservation(array $attrs = []): Reservation
    {
        return Reservation::create(array_merge([
            'team_id' => $this->team->id,
            'code' => 'RES-' . rand(100, 999),
            'check_in' => now()->subDays(3),
            'check_out' => now()->subDays(1),
            'status' => 'cancelled',
            'cancellation_reason' => 'Guest request',
        ], $attrs));
    }

    public function test_cancellation_list_displays()
    {
        $this->makeReservation(['status' => 'cancelled', 'cancellation_reason' => 'Guest request']);
        $this->makeReservation(['status' => 'no_show', 'cancellation_reason' => null]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/reservations/cancellations');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'stats'])
            ->assertJsonPath('stats.total', 2)
            ->assertJsonPath('stats.cancelled', 1)
            ->assertJsonPath('stats.no_show', 1);
    }

    public function test_cancellation_list_filters_by_type()
    {
        $this->makeReservation(['status' => 'cancelled']);
        $this->makeReservation(['status' => 'no_show']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/reservations/cancellations?type=no_show');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('no_show', $response->json('data.0.status'));
    }

    public function test_cancellation_list_filters_by_reason()
    {
        $this->makeReservation(['cancellation_reason' => 'Flight cancelled']);
        $this->makeReservation(['cancellation_reason' => 'Changed plans']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/reservations/cancellations?reason=Flight');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_cancellation_list_filters_by_date()
    {
        $this->makeReservation();

        $response = $this->actingAs($this->user)
            ->getJson('/api/reservations/cancellations?date=' . now()->toDateString());

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_cancellation_show_returns_details()
    {
        $res = $this->makeReservation(['status' => 'cancelled', 'cancellation_reason' => 'No payment']);

        $response = $this->actingAs($this->user)
            ->getJson("/api/reservations/cancellations/{$res->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $res->id)
            ->assertJsonPath('data.status', 'cancelled');
    }

    public function test_only_cancelled_and_noshow_are_returned()
    {
        $this->makeReservation(['status' => 'cancelled']);
        Reservation::create([
            'team_id' => $this->team->id,
            'code' => 'RES-ACTIVE',
            'check_in' => now(),
            'check_out' => now()->addDays(2),
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/reservations/cancellations');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_unauthenticated_request_returns_401()
    {
        $this->getJson('/api/reservations/cancellations')->assertStatus(401);
    }
}
