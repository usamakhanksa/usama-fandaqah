<?php

namespace Tests\Feature;

use App\Models\NightAuditLog;
use App\Models\NightAuditOccupancySnapshot;
use App\Team;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class NightAuditRerunTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;
    protected $team;
    protected $oldLog;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTeamAndUser();
        $this->seedPermissions();
    }

    private function seedPermissions()
    {
        \DB::table('permissions')->insertOrIgnore([
            ['slug' => 'run night audit', 'name' => 'Run Night Audit', 'group' => 'Night Audit', 'module' => 'Night Audit', 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'rerun night audit', 'name' => 'Rerun Night Audit', 'group' => 'Night Audit', 'module' => 'Night Audit', 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'rerun historical night audit', 'name' => 'Rerun Historical Night Audit', 'group' => 'Night Audit', 'module' => 'Night Audit', 'created_at' => now(), 'updated_at' => now()],
        ]);
        
        $roleId = \DB::table('roles')->where('team_id', $this->team->id)->where('slug', 'admin-'.$this->team->id)->value('id');
        
        \DB::table('role_permission')->insertOrIgnore([
            ['role_id' => $roleId, 'permission_slug' => 'run night audit', 'created_at' => now(), 'updated_at' => now()],
            ['role_id' => $roleId, 'permission_slug' => 'rerun night audit', 'created_at' => now(), 'updated_at' => now()],
            ['role_id' => $roleId, 'permission_slug' => 'rerun historical night audit', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    private function setupTeamAndUser()
    {
        $this->user = User::create([
            'name' => 'Rerun Admin',
            'email' => 'rerun_'.rand().'@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->team = Team::create([
            'name' => 'Rerun Hotel',
            'slug' => 'rerun-hotel-' . rand(),
            'owner_id' => $this->user->id,
            'business_date' => '2026-05-10',
            'night_audit_auto_enabled' => true
        ]);
        
        $this->user->update(['current_team_id' => $this->team->id]);

        // Create a previous audit log (yesterday)
        $snapshot = NightAuditOccupancySnapshot::create([
            'team_id' => $this->team->id,
            'business_date' => '2026-05-09',
            'run_number' => 1,
            'is_final' => true,
            'total_rooms' => 10,
            'rooms_available' => 10,
            'rooms_occupied' => 0,
            'rooms_cleaning' => 0,
            'rooms_maintenance' => 0,
            'occupancy_pct' => 0,
            'adr' => 0,
            'revpar' => 0,
            'arrivals_count' => 0,
            'departures_count' => 0,
            'room_revenue' => 0,
            'total_revenue' => 0,
        ]);

        $this->oldLog = NightAuditLog::create([
            'team_id' => $this->team->id,
            'business_date' => '2026-05-09',
            'run_number' => 1,
            'status' => 'completed',
            'triggered_by' => 'manual',
            'occupancy_snapshot_id' => $snapshot->id,
            'started_at' => now()->subDay(),
            'completed_at' => now()->subDay(),
        ]);
    }

    public function test_rerun_recent_audit_succeeds()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/night-audit/rerun/{$this->oldLog->id}");

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('night_audit_log', [
            'business_date' => '2026-05-09',
            'run_number' => 2,
            'status' => 'completed',
            'rerun_of_log_id' => $this->oldLog->id
        ]);

        $this->assertDatabaseHas('night_audit_occupancy_snapshot', [
            'id' => $this->oldLog->occupancy_snapshot_id,
            'is_final' => false
        ]);
    }

    public function test_rerun_historical_audit_fails_without_reason()
    {
        // Move business date far forward
        $this->team->update(['business_date' => '2026-06-01']); // > 20 days later

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/night-audit/rerun/{$this->oldLog->id}");

        $response->assertStatus(422);
        $response->assertJsonFragment(['message' => 'A mandatory reason is required for historical reruns (8-30 days).']);
    }

    public function test_rerun_very_old_audit_is_blocked()
    {
        // Move business date 40 days forward
        $this->team->update(['business_date' => '2026-06-20']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/night-audit/rerun/{$this->oldLog->id}");

        $response->assertStatus(422);
        $response->assertJsonFragment(['message' => 'Rerunning audits older than 30 days is blocked.']);
    }

    public function test_rerun_historical_audit_succeeds_with_reason()
    {
        // Move business date 10 days forward
        $this->team->update(['business_date' => '2026-05-20']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/night-audit/rerun/{$this->oldLog->id}", [
                'reason' => 'Fixing revenue miscalculation'
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('night_audit_log', [
            'business_date' => '2026-05-09',
            'notes' => 'Fixing revenue miscalculation'
        ]);
    }
}
