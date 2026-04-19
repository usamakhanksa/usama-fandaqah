<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FinancialManagementApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_financial_modules_can_be_listed_and_receipt_can_be_confirmed(): void
    {
        $this->seed();
        Sanctum::actingAs(User::first());

        $this->getJson('/api/financial/receipts')
            ->assertOk()
            ->assertJsonStructure(['metrics', 'rows' => ['data']]);

        $this->postJson('/api/financial/receipt/confirm', [
            'amount' => 500,
            'receipt_type' => 'Rent',
            'receipt_code' => '22332',
            'reason' => 'Rent',
            'date' => now()->toDateString(),
            'payment_method' => 'Cash',
            'received_by' => 'Aya Ahmed Abdullah',
        ])->assertCreated();

        $this->getJson('/api/financial/fund-movement?view=receipt')
            ->assertOk()
            ->assertJsonStructure(['metrics', 'rows' => ['data']]);
    }
}
