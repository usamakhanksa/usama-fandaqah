<?php

namespace Tests\Feature;

use Tests\TestCase;

class PosApiTest extends TestCase
{
    public function test_pos_endpoints_require_authentication(): void
    {
        $this->getJson('/api/pos/stores')->assertStatus(401);
        $this->getJson('/api/pos/categories')->assertStatus(401);
        $this->getJson('/api/pos/sub-categories')->assertStatus(401);
        $this->getJson('/api/pos/brands')->assertStatus(401);
        $this->getJson('/api/pos/products')->assertStatus(401);
        $this->getJson('/api/pos/services')->assertStatus(401);
        $this->postJson('/api/pos/services', [])->assertStatus(401);
        $this->getJson('/api/pos/transactions')->assertStatus(401);
        $this->getJson('/api/pos/cart')->assertStatus(401);
        $this->postJson('/api/pos/cart/items', [])->assertStatus(401);
        $this->deleteJson('/api/pos/cart/items')->assertStatus(401);
        $this->postJson('/api/pos/checkout', [])->assertStatus(401);
    }
}
