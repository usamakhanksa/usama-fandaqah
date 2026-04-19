<?php

namespace Tests\Feature;

use Tests\TestCase;

class RoomsApiTest extends TestCase
{
    public function test_rooms_metrics_requires_auth(): void
    {
        $this->getJson('/api/rooms/metrics')->assertStatus(401);
    }

    public function test_rooms_list_requires_auth(): void
    {
        $this->getJson('/api/rooms')->assertStatus(401);
    }

    public function test_room_crud_requires_auth(): void
    {
        $this->postJson('/api/rooms', [])->assertStatus(401);
        $this->putJson('/api/rooms/1', [])->assertStatus(401);
        $this->deleteJson('/api/rooms/1')->assertStatus(401);
    }

    public function test_rooms_availability_requires_auth(): void
    {
        $this->getJson('/api/rooms/availability/list')->assertStatus(401);
    }
}
