<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PersonCompanyFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_person_and_company_submission(): void
    {
        $this->seed();
        $role = Role::firstOrCreate(['slug' => 'super-admin'], ['name' => 'Super Admin']);
        $user = User::query()->create(['role_id' => $role->id, 'name' => 'Tester', 'email' => 'tester@demo.com', 'password' => bcrypt('password')]);
        Sanctum::actingAs($user);

        $guest = $this->postJson('/api/guests', [
            'name' => 'Ahmed Mohamed',
            'phone' => '+966 123456789',
            'email' => 'ahmed@example.com',
            'type' => 'vip',
            'gender' => 'male',
            'card_id' => '123456',
            'read_only_field' => 'Read Only',
        ])->assertOk();

        $this->assertEquals('Ahmed Mohamed', $guest->json('data.name'));

        $this->postJson('/api/companies', [
            'company_name' => 'Sure LLC',
            'email' => 'sure@example.com',
            'city_id' => 1,
            'country_id' => 1,
        ])->assertOk()->assertJsonPath('data.company_name', 'Sure LLC');
    }
}
