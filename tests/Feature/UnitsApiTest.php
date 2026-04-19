<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UnitsApiTest extends TestCase
{
    use RefreshDatabase;

    private function auth(): void
    {
        $role = Role::firstOrCreate(['slug' => 'super-admin'], ['name' => 'Super Admin']);
        $user = User::query()->create([
            'role_id' => $role->id,
            'name' => 'Units Tester',
            'email' => 'units@example.com',
            'password' => bcrypt('password'),
        ]);

        Sanctum::actingAs($user);
    }

    public function test_units_endpoints_require_auth(): void
    {
        $this->getJson('/api/units/floors')->assertStatus(401);
        $this->getJson('/api/units/daily-status')->assertStatus(401);
        $this->postJson('/api/units/check-in', [])->assertStatus(401);
        $this->postJson('/api/units/check-out', [])->assertStatus(401);
    }

    public function test_units_core_endpoints_work(): void
    {
        $this->seed();
        $this->auth();

        $this->getJson('/api/units/filters')->assertOk()->assertJsonStructure(['statuses', 'types']);
        $this->getJson('/api/units/floors')->assertOk()->assertJsonStructure([['id', 'name', 'count', 'units', 'legend']]);
        $this->getJson('/api/units/daily-status')->assertOk()->assertJsonStructure(['arrivals', 'departures']);
    }

    public function test_check_in_and_check_out_transactions_work(): void
    {
        $this->seed();
        $this->auth();

        $reservationId = 1;
        $unitId = 1;

        $this->postJson('/api/units/check-in', [
            'reservation_id' => $reservationId,
            'unit_id' => $unitId,
            'date' => now()->toDateString(),
            'time' => '10:00 AM',
            'note' => 'Arrived',
        ])->assertCreated();

        $this->postJson('/api/units/check-out', [
            'reservation_id' => $reservationId,
            'unit_id' => $unitId,
            'date' => now()->toDateString(),
            'time' => '01:00 PM',
            'note' => 'Left',
            'final_charges' => 25,
        ])->assertCreated();
    }
}
