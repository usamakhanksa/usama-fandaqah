<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\User;
use App\Models\Unit;
use App\Models\Customer;
use App\Models\Building;
use App\Models\LongStayContract;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LongStayTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $team;

    protected function setUp(): void
    {
        parent::setUp();
        $this->team = Team::factory()->create();
        $this->user = User::factory()->create(['current_team_id' => $this->team->id]);
        $this->team->users()->attach($this->user);
    }

    public function test_can_list_buildings()
    {
        Building::factory()->count(3)->create(['team_id' => $this->team->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/long-stay/buildings');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_can_create_building()
    {
        $data = [
            'name_en' => 'Building A',
            'name_ar' => 'مبنى أ',
            'total_floors' => 5,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/long-stay/buildings', $data);

        $response->assertStatus(201)
            ->assertJsonPath('name_en', 'Building A');
        
        $this->assertDatabaseHas('buildings', ['name_en' => 'Building A']);
    }

    public function test_can_create_long_stay_contract()
    {
        $unit = Unit::factory()->create(['team_id' => $this->team->id]);
        $customer = Customer::factory()->create(['team_id' => $this->team->id]);

        $data = [
            'unit_id' => $unit->id,
            'customer_id' => $customer->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(6)->toDateString(),
            'billing_cycle' => 'monthly',
            'amount' => 5000,
            'security_deposit' => 1000,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/long-stay/contracts', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('long_stay_contracts', ['amount' => 5000]);
    }

    public function test_can_terminate_contract()
    {
        $contract = LongStayContract::factory()->create([
            'team_id' => $this->team->id,
            'status' => 'active'
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/long-stay/contracts/{$contract->id}/terminate");

        $response->assertStatus(200);
        $this->assertEquals('terminated', $contract->fresh()->status);
    }
}
