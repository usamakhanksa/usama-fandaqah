<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GuestsApiTest extends TestCase
{
    use RefreshDatabase;

    private function authUser(): User
    {
        $role = Role::firstOrCreate(['slug' => 'super-admin'], ['name' => 'Super Admin']);
        $user = User::query()->create(['role_id' => $role->id, 'name' => 'Tester', 'email' => 'test@example.com', 'password' => bcrypt('password')]);
        Sanctum::actingAs($user);

        return $user;
    }

    public function test_guest_and_company_endpoints_work(): void
    {
        $this->seed();
        $this->authUser();

        $this->getJson('/api/guests')->assertOk()->assertJsonStructure(['data', 'meta']);
        $this->getJson('/api/companies')->assertOk()->assertJsonStructure(['data', 'meta']);
    }

    public function test_company_draft_can_be_saved(): void
    {
        $this->seed();
        $this->authUser();

        $this->postJson('/api/companies/drafts', ['payload' => ['company_name' => 'Draft Co']])
            ->assertCreated()
            ->assertJsonPath('payload.company_name', 'Draft Co');
    }
}
