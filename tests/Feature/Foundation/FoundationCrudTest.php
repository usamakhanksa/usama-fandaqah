<?php

namespace Tests\Feature\Foundation;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FoundationCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_foundation_index_routes_are_accessible(): void
    {
        $this->get('/foundation/unit-categories')->assertOk();
        $this->get('/foundation/units')->assertOk();
        $this->get('/foundation/customers')->assertOk();
        $this->get('/foundation/reservations')->assertOk();
    }
}
