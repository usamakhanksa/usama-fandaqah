<?php

namespace Tests\Feature;

use Tests\TestCase;

class ReservationWorkflowTest extends TestCase
{
    public function test_reservation_workflow_endpoints_require_auth(): void
    {
        $this->getJson('/api/reservations/schedule')->assertStatus(401);
        $this->postJson('/api/reservations/drafts', [])->assertStatus(401);
        $this->postJson('/api/reservations/promo/apply', [])->assertStatus(401);
    }
}
