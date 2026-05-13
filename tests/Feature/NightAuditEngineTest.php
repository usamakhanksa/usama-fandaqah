<?php

namespace Tests\Feature;

use App\User;
use App\Role;
use App\Team;
use App\Models\Reservation;
use App\Models\NightAuditLog;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Carbon\Carbon;

class NightAuditEngineTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissions();
    }

    private function seedPermissions()
    {
        \DB::table('permissions')->insertOrIgnore([
            ['slug' => 'view night audit', 'name' => 'View Night Audit', 'group' => 'Night Audit', 'module' => 'Night Audit', 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'run night audit', 'name' => 'Run Night Audit', 'group' => 'Night Audit', 'module' => 'Night Audit', 'created_at' => now(), 'updated_at' => now()],
        ]);
        
        \DB::table('role_permission')->insertOrIgnore([
            ['role_id' => 1, 'permission_slug' => 'view night audit', 'created_at' => now(), 'updated_at' => now()],
            ['role_id' => 1, 'permission_slug' => 'run night audit', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    private function setupTeamAndUser()
    {
        $user = User::create([
            'name' => 'Audit Admin',
            'email' => 'audit_'.rand().'@example.com',
            'password' => bcrypt('password'),
        ]);

        $team = Team::create([
            'name' => 'Test Hotel',
            'slug' => 'test-hotel-' . rand(),
            'owner_id' => $user->id,
            'business_date' => '2026-04-30',
            'night_audit_auto_enabled' => true
        ]);
        
        $user->update(['current_team_id' => $team->id]);

        $role = $team->roles()->where('slug', 'admin')->first();
        if (!$role) {
            $role = Role::create([
                'name' => 'Admin Role',
                'slug' => 'admin-' . rand(),
                'team_id' => $team->id
            ]);
        }

        \DB::table('role_permission')->insertOrIgnore([
            ['role_id' => $role->id, 'permission_slug' => 'view night audit', 'created_at' => now(), 'updated_at' => now()],
            ['role_id' => $role->id, 'permission_slug' => 'run night audit', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $user->roles()->syncWithoutDetaching([$role->id]);
        return [$team, $user->fresh(['roles', 'roles.getPermissions'])];
    }

    public function test_preflight_fails_with_open_shifts()
    {
        [$team, $user] = $this->setupTeamAndUser();

        \DB::table('cashier_shifts')->insert([
            'team_id' => $team->id,
            'user_id' => $user->id,
            'shift_date' => '2026-04-30',
            'status' => 'open',
            'opened_at' => now()
        ]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/night-audit/preflight');
        $response->assertStatus(200);
        $response->assertJson(['can_run' => false]);
        $response->assertJsonFragment(['There are 1 open cashier shifts that must be closed.']);
    }

    public function test_audit_runs_successfully_and_advances_date()
    {
        [$team, $user] = $this->setupTeamAndUser();

        // Ensure no blockers
        $response = $this->actingAs($user, 'sanctum')->postJson('/api/night-audit/run');
        
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $team->refresh();
        $this->assertEquals('2026-05-01', $team->business_date);

        $this->assertDatabaseHas('night_audit_log', [
            'team_id' => $team->id,
            'business_date' => '2026-04-30',
            'status' => 'completed'
        ]);
    }

    public function test_transactions_are_frozen_after_audit()
    {
        [$team, $user] = $this->setupTeamAndUser();

        $txId = \DB::table('transactions')->insertGetId([
            'team_id' => $team->id,
            'payable_type' => 'App\User',
            'payable_id' => $user->id,
            'type' => 'withdraw',
            'amount' => 10000,
            'confirmed' => true,
            'is_freezed' => false,
            'uuid' => \Illuminate\Support\Str::uuid(),
            'created_at' => '2026-04-30 10:00:00'
        ]);

        $this->actingAs($user, 'sanctum')->postJson('/api/night-audit/run');

        $this->assertDatabaseHas('transactions', [
            'id' => $txId,
            'is_freezed' => true
        ]);

        $this->assertDatabaseHas('business_date_transactions', [
            'transaction_id' => $txId,
            'business_date' => '2026-04-30'
        ]);
    }
}
