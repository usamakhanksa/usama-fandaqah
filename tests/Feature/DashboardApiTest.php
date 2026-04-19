<?php
namespace Tests\Feature;
use Tests\TestCase;
class DashboardApiTest extends TestCase {
  public function test_dashboard_summary_endpoint_requires_auth(): void { $this->getJson('/api/dashboard/summary')->assertStatus(401); }
}
