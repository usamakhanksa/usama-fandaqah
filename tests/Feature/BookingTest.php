<?php
namespace Tests\Feature;
use Tests\TestCase;
class BookingTest extends TestCase {
  public function test_booking_store_requires_auth(): void { $this->postJson('/api/bookings',[])->assertStatus(401); }
}
