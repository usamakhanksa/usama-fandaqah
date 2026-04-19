<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserGroupingApiTest extends TestCase
{
    use RefreshDatabase;

    private function authUser(): void
    {
        $role = Role::firstOrCreate(['slug' => 'super-admin'], ['name' => 'Super Admin']);
        $user = User::query()->create([
            'role_id' => $role->id,
            'name' => 'Tester',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);
        Sanctum::actingAs($user);
    }

    public function test_roles_and_permissions_endpoints_work(): void
    {
        $this->seed();
        $this->authUser();

        $roles = $this->getJson('/api/user-groups/roles')->assertOk()->json('data');
        $this->assertNotEmpty($roles);

        $roleId = $roles[0]['id'];
        $this->getJson("/api/user-groups/roles/{$roleId}/permissions")
            ->assertOk()
            ->assertJsonStructure(['data' => [['id', 'name', 'enabled', 'anyone', 'create', 'edit', 'view', 'remove']]]);
    }

    public function test_role_crud_duplicate_assign_and_permission_update_work(): void
    {
        $this->seed();
        $this->authUser();

        $role = $this->postJson('/api/user-groups/roles', ['name' => 'Test Role'])->assertCreated()->json('data');
        $roleId = $role['id'];

        $this->putJson("/api/user-groups/roles/{$roleId}", ['name' => 'Updated Role'])->assertOk();
        $copy = $this->postJson("/api/user-groups/roles/{$roleId}/duplicate")->assertCreated()->json('data');

        $users = $this->getJson('/api/user-groups/users')->assertOk()->json('data');
        $ids = array_column(array_slice($users, 0, 2), 'id');
        $this->postJson("/api/user-groups/roles/{$roleId}/assign-users", ['user_ids' => $ids])->assertOk();

        $permission = $this->getJson("/api/user-groups/roles/{$roleId}/permissions")->json('data.0');
        $this->putJson("/api/user-groups/roles/{$roleId}/permissions/{$permission['id']}", [
            'enabled' => true,
            'anyone' => true,
            'create' => true,
            'edit' => false,
            'view' => true,
            'remove' => false,
        ])->assertOk();

        $this->deleteJson("/api/user-groups/roles/{$copy['id']}")->assertOk();
    }
}
