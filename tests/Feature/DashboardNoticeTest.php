<?php

namespace Tests\Feature;

use App\Models\DashboardNotice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardNoticeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that active notices are returned via the dashboard KPIs endpoint.
     */
    public function test_dashboard_endpoint_returns_active_notices()
    {
        $user = User::factory()->create();
        
        DashboardNotice::factory()->create([
            'title' => 'Important Maintenance',
            'is_active' => true,
        ]);

        DashboardNotice::factory()->inactive()->create([
            'title' => 'Old Notice',
        ]);

        $response = $this->actingAs($user)->getJson('/api/dashboard/kpis');

        $response->assertStatus(200)
                 ->assertJsonFragment(['title' => 'Important Maintenance'])
                 ->assertJsonMissing(['title' => 'Old Notice']);
    }

    /**
     * Test that an admin can create a notice.
     */
    public function test_authorized_user_can_create_notice()
    {
        $user = User::factory()->create();
        // Mock permission check if necessary, or ensure seeder has run
        // For simplicity in this test, we assume the user has permission 
        // or the policy is updated to allow this user.
        
        $data = [
            'title' => 'New Season Promo',
            'content' => 'Special 20% discount for all bookings.',
            'type' => 'info',
            'is_active' => true,
        ];

        $response = $this->actingAs($user)->postJson('/api/dashboard-notices', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('dashboard_notices', ['title' => 'New Season Promo']);
    }

    /**
     * Test that notices can be deleted.
     */
    public function test_authorized_user_can_delete_notice()
    {
        $user = User::factory()->create();
        $notice = DashboardNotice::factory()->create();

        $response = $this->actingAs($user)->deleteJson("/api/dashboard-notices/{$notice->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('dashboard_notices', ['id' => $notice->id]);
    }
}
