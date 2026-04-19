<?php
namespace Tests\Feature;
use Tests\TestCase;
class ReservationsTest extends TestCase {
  public function test_reservations_endpoint_requires_auth(): void { $this->getJson('/api/reservations')->assertStatus(401); }
}
