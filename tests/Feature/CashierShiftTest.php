<?php

namespace Tests\Feature;

use App\Models\CashierShift;
use App\Transaction;
use App\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashierShiftTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup permissions
        $this->artisan('db:seed', ['--class' => 'CashierShiftPermissionSeeder']);
    }

    public function test_cashier_can_open_shift()
    {
        $user = User::factory()->create();
        $this->assignPermission($user, 'cashier.open_shift');

        $response = $this->actingAs($user)->postJson('/api/cashier-shifts/open', [
            'opening_balance' => 500,
            'notes' => 'Opening morning shift'
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('cashier_shifts', [
            'user_id' => $user->id,
            'opening_balance' => 500,
            'status' => 'open'
        ]);
    }

    public function test_cashier_cannot_open_multiple_shifts()
    {
        $user = User::factory()->create();
        $this->assignPermission($user, 'cashier.open_shift');

        CashierShift::create([
            'user_id' => $user->id,
            'opened_at' => now(),
            'status' => 'open',
            'opening_balance' => 500,
            'team_id' => 1
        ]);

        $response = $this->actingAs($user)->postJson('/api/cashier-shifts/open', [
            'opening_balance' => 600
        ]);

        $response->assertStatus(422);
    }

    public function test_cashier_can_close_shift()
    {
        $user = User::factory()->create();
        $this->assignPermission($user, 'cashier.close_shift');

        $shift = CashierShift::create([
            'user_id' => $user->id,
            'opened_at' => now(),
            'status' => 'open',
            'opening_balance' => 100,
            'team_id' => 1
        ]);

        // Create a transaction
        Transaction::create([
            'created_by' => $user->id,
            'amount' => 50,
            'type' => 'deposit',
            'cashier_shift_id' => $shift->id,
            'team_id' => 1,
            'payable_type' => 'App\Team',
            'payable_id' => 1
        ]);

        $response = $this->actingAs($user)->postJson("/api/cashier-shifts/{$shift->id}/close", [
            'closing_balance' => 150
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('cashier_shifts', [
            'id' => $shift->id,
            'status' => 'closed',
            'system_balance' => 150,
            'variance' => 0
        ]);
    }

    protected function assignPermission($user, $slug)
    {
        $role = Role::firstOrCreate(['slug' => 'cashier', 'name' => 'Cashier']);
        $permission = Permission::where('slug', $slug)->first();
        if ($permission) {
            $role->permissions()->syncWithoutDetaching([$permission->id => ['enabled' => 1]]);
        }
        $user->roles()->attach($role);
    }
}
