<?php

namespace Tests\Unit\Foundation;

use App\Policies\Foundation\FoundationPolicy;
use PHPUnit\Framework\TestCase;

class FoundationPolicyTest extends TestCase
{
    public function test_policy_allows_crud(): void
    {
        $policy = new FoundationPolicy();
        $this->assertTrue($policy->viewAny());
        $this->assertTrue($policy->create());
        $this->assertTrue($policy->update());
        $this->assertTrue($policy->delete());
    }
}
