<?php

namespace Tests\Feature;

use App\User;
use App\Role;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
    }

    private function createUserWithPermission($permission)
    {
        $user = User::create([
            'name' => 'Dashboard User',
            'email' => 'dash_'.rand().'@example.com',
            'password' => bcrypt('password'),
            'current_team_id' => 1
        ]);
        
        $role = Role::create([
            'name' => 'Dashboard Role',
            'slug' => 'dashboard-role-'.rand(),
            'team_id' => 1
        ]);
        
        \DB::table('role_permission')->insert([
            'role_id' => $role->id,
            'permission_slug' => $permission,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $user->roles()->attach($role->id);
        return $user->fresh(['roles', 'roles.getPermissions']);
    }

    public function test_overview_dashboard_returns_data()
    {
        $user = $this->createUserWithPermission('view dashboard');
        $this->withoutExceptionHandling();
        $response = $this->actingAs($user, 'sanctum')->getJson('/api/dashboard/overview');
        $response->assertStatus(200);
        $response->assertJsonStructure(['metrics', 'chart', 'rooms']);
    }

    public function test_night_audit_dashboard_returns_data()
    {
        $user = $this->createUserWithPermission('view night audit');
        $response = $this->actingAs($user, 'sanctum')->getJson('/api/dashboard/night-audit');
        $response->assertStatus(200);
        $response->assertJsonStructure(['metrics', 'status', 'checklist']);
    }

    public function test_occupancy_dashboard_returns_data()
    {
        $user = $this->createUserWithPermission('view occupancy dashboard');
        $response = $this->actingAs($user, 'sanctum')->getJson('/api/dashboard/occupancy');
        $response->assertStatus(200);
        $response->assertJsonStructure(['metrics', 'chart']);
    }

    public function test_revenue_dashboard_returns_data()
    {
        $user = $this->createUserWithPermission('view revenue dashboard');
        $response = $this->actingAs($user, 'sanctum')->getJson('/api/dashboard/revenue');
        $response->assertStatus(200);
        $response->assertJsonStructure(['metrics', 'chart']);
    }

    public function test_ar_dashboard_returns_data()
    {
        $user = $this->createUserWithPermission('view ar dashboard');
        $response = $this->actingAs($user, 'sanctum')->getJson('/api/dashboard/ar');
        $response->assertStatus(200);
        $response->assertJsonStructure(['metrics', 'chart', 'topDebtors']);
    }

    public function test_cashier_dashboard_returns_data()
    {
        $user = $this->createUserWithPermission('view cashier shifts');
        $response = $this->actingAs($user, 'sanctum')->getJson('/api/dashboard/cashier');
        $response->assertStatus(200);
        $response->assertJsonStructure(['metrics', 'shifts']);
    }

    public function test_commissions_dashboard_returns_data()
    {
        $user = $this->createUserWithPermission('view commissions');
        $response = $this->actingAs($user, 'sanctum')->getJson('/api/dashboard/commissions');
        $response->assertStatus(200);
        $response->assertJsonStructure(['metrics', 'agents']);
    }

    public function test_metabase_dashboard_returns_data()
    {
        $user = $this->createUserWithPermission('view metabase reports');
        $response = $this->actingAs($user, 'sanctum')->getJson('/api/dashboard/metabase');
        $response->assertStatus(200);
        $response->assertJsonStructure(['iframeUrl']);
    }
}
