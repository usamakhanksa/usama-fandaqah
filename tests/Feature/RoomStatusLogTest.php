<?php

namespace Tests\Feature;

use App\Models\Unit;
use App\Models\RoomStatusLog;
use App\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomStatusLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RoomStatusPermissionSeeder']);
    }

    public function test_manual_status_change_is_logged()
    {
        $user = User::factory()->create();
        $this->assignPermission($user, 'room_status.view');
        
        $unit = Unit::factory()->create(['status' => 'available', 'team_id' => 1]);

        // Act as user and change status manually
        $unit->update(['status' => 'maintenance']);

        $this->assertDatabaseHas('room_status_logs', [
            'unit_id' => $unit->id,
            'from_status' => 'available',
            'to_status' => 'maintenance',
            'change_reason' => 'Manual change'
        ]);
    }

    public function test_user_can_view_logs()
    {
        $user = User::factory()->create(['team_id' => 1]);
        $this->assignPermission($user, 'room_status.view');

        RoomStatusLog::create([
            'unit_id' => 1,
            'team_id' => 1,
            'from_status' => 'available',
            'to_status' => 'dirty',
            'changed_by' => $user->id,
            'change_reason' => 'Test log',
            'changed_at' => now()
        ]);

        $response = $this->actingAs($user)->getJson('/api/room-status-logs');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    protected function assignPermission($user, $slug)
    {
        $role = Role::firstOrCreate(['slug' => 'admin', 'name' => 'Admin']);
        $permission = Permission::where('slug', $slug)->first();
        if ($permission) {
            $role->permissions()->syncWithoutDetaching([$permission->id => ['enabled' => 1]]);
        }
        $user->roles()->attach($role);
    }
}
