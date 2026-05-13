<?php

namespace Tests\Feature;

use App\User;
use App\Role;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class SidebarPermissionTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock config for testing
        Config::set('sidebar', [
            [
                'key' => 'dashboard',
                'label_en' => 'Dashboard',
                'route' => '/dashboard',
                'permission' => 'view dashboard',
                'order' => 10,
                'children' => []
            ],
            [
                'key' => 'settings',
                'label_en' => 'Settings',
                'route' => '/settings',
                'permission' => 'view settings',
                'order' => 20,
                'children' => []
            ]
        ]);
    }

    public function test_sidebar_returns_only_permitted_items()
    {
        // 1. Create a user
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test_'.rand().'@example.com',
            'password' => bcrypt('password'),
            'current_team_id' => 1
        ]);
        
        // 2. Create a role with only 'view dashboard' permission
        $role = Role::create([
            'name' => 'Limited Role',
            'slug' => 'limited-role',
            'team_id' => 1
        ]);
        
        \DB::table('role_permission')->insert([
            'role_id' => $role->id,
            'permission_slug' => 'view dashboard',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $user->roles()->attach($role->id);
        $user = $user->fresh(['roles', 'roles.getPermissions']);

        // 3. Make request to /api/sidebar
        $this->withoutExceptionHandling();
        $response = $this->actingAs($user, 'sanctum')->getJson('/api/sidebar');
        
        $response->assertStatus(200);
        $data = $response->json('data');
        
        // Should only see dashboard, not settings
        $this->assertCount(1, $data);
        $this->assertEquals('dashboard', $data[0]['key']);
    }

    public function test_unauthenticated_user_gets_empty_sidebar()
    {
        $response = $this->getJson('/api/sidebar');
        $response->assertStatus(401);
    }
}
