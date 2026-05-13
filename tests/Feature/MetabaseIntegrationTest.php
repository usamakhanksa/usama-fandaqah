<?php

namespace Tests\Feature;

use App\User;
use App\Team;
use Tests\TestCase;
use Illuminate\Support\Facades\Config;

class MetabaseIntegrationTest extends TestCase
{
    public function test_metabase_signed_url_generation()
    {
        // Mock config
        Config::set('services.metabase.url', 'http://metabase.test');
        Config::set('services.metabase.secret_key', 'test_secret_key');

        $user = User::factory()->create();
        $user->givePermissionTo('reports.view');

        $response = $this->actingAs($user)->getJson('/api/reports/metabase/1');

        $response->assertStatus(200);
        $response->assertJsonStructure(['url']);
        
        $url = $response->json('url');
        $this->assertStringContainsString('http://metabase.test/embed/dashboard/', $url);
    }

    public function test_metabase_unauthorized_access()
    {
        $user = User::factory()->create();
        // No permission

        $response = $this->actingAs($user)->getJson('/api/reports/metabase/1');

        $response->assertStatus(403);
    }
}
